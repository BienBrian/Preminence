<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\ModuleInvoiceItem;
use App\Models\TenantModule;
use App\Models\TenantModuleSubscription;
use App\Services\Modules\BillingScheduleService;
use App\Services\Payment\PayStackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * PayStack Webhook Controller
 * 
 * Handles all PayStack webhook events.
 * @see https://paystack.com/docs/payments/webhooks/
 */
class PayStackWebhookController extends Controller
{
    private PayStackService $payStackService;
    private BillingScheduleService $scheduleService;

    public function __construct(
        PayStackService $payStackService,
        BillingScheduleService $scheduleService
    ) {
        $this->payStackService = $payStackService;
        $this->scheduleService = $scheduleService;
    }

    /**
     * Handle incoming PayStack webhook.
     */
    public function handle(Request $request)
    {
        // Get signature from header
        $signature = $request->header('x-paystack-signature');
        
        if (!$signature) {
            Log::warning('PayStack webhook missing signature');
            return response()->json(['error' => 'Missing signature'], 401);
        }

        // Get raw payload
        $payload = $request->getContent();

        // Verify signature
        if (!$this->payStackService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('PayStack webhook invalid signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Parse event
        $event = $request->json('event');
        $data = $request->json('data');

        Log::info('PayStack webhook received', [
            'event' => $event,
            'reference' => $data['reference'] ?? null,
        ]);

        // Handle event
        try {
            match ($event) {
                'charge.success' => $this->handleChargeSuccess($data),
                'charge.failed' => $this->handleChargeFailed($data),
                'refund.processed' => $this->handleRefundProcessed($data),
                'subscription.create' => $this->handleSubscriptionCreated($data),
                'subscription.disable' => $this->handleSubscriptionDisabled($data),
                'invoice.create' => $this->handleInvoiceCreated($data),
                'invoice.update' => $this->handleInvoiceUpdated($data),
                default => Log::info("Unhandled PayStack event: {$event}"),
            };

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('PayStack webhook handler error', [
                'event' => $event,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Handler error'], 500);
        }
    }

    /**
     * Handle successful charge.
     */
    private function handleChargeSuccess(array $data): void
    {
        $reference = $data['reference'];
        $metadata = $data['metadata'] ?? [];

        Log::info('Processing successful charge', ['reference' => $reference]);

        // Check if this is a module payment
        if (!empty($metadata['invoice_item_id'])) {
            $invoiceItem = ModuleInvoiceItem::find($metadata['invoice_item_id']);
            
            if ($invoiceItem) {
                $invoiceItem->markAsPaid(
                    $data['channel'] ?? 'paystack',
                    'PAYSTACK_' . $reference
                );

                // Update subscription if recurring
                if ($invoiceItem->isRecurring() && $invoiceItem->subscription) {
                    $subscription = $invoiceItem->subscription;
                    $subscription->update([
                        'last_billed_at' => now(),
                        'next_billing_at' => $this->scheduleService->calculateNextBillingDate($subscription),
                    ]);
                }

                // Activate module if initial payment
                if ($invoiceItem->type === 'prorated_charge' || $invoiceItem->type === 'setup_fee') {
                    $this->activateModule($invoiceItem);
                }
            }
        }
    }

    /**
     * Handle failed charge.
     */
    private function handleChargeFailed(array $data): void
    {
        $reference = $data['reference'];
        $metadata = $data['metadata'] ?? [];

        Log::warning('Charge failed', [
            'reference' => $reference,
            'message' => $data['gateway_response'] ?? 'Unknown error',
        ]);

        if (!empty($metadata['invoice_item_id'])) {
            $invoiceItem = ModuleInvoiceItem::find($metadata['invoice_item_id']);
            
            if ($invoiceItem) {
                $invoiceItem->markAsFailed();
                
                // Increment retry count on subscription
                if ($invoiceItem->subscription) {
                    $subscription = $invoiceItem->subscription;
                    $retries = ($subscription->metadata['billing_retries'] ?? 0) + 1;
                    $subscription->update([
                        'metadata' => array_merge(
                            $subscription->metadata ?? [],
                            ['billing_retries' => $retries]
                        ),
                    ]);
                }
            }
        }
    }

    /**
     * Handle refund processed.
     */
    private function handleRefundProcessed(array $data): void
    {
        $transactionReference = $data['transaction_reference'] ?? null;
        
        if (!$transactionReference) {
            return;
        }

        // Find original invoice
        $originalInvoice = ModuleInvoiceItem::where('transaction_id', 'PAYSTACK_' . $transactionReference)
            ->first();

        if ($originalInvoice && $data['status'] === 'processed') {
            $originalInvoice->markAsRefunded();
        }
    }

    /**
     * Handle subscription created (PayStack subscription).
     */
    private function handleSubscriptionCreated(array $data): void
    {
        Log::info('PayStack subscription created', ['code' => $data['subscription_code'] ?? null]);
    }

    /**
     * Handle subscription disabled.
     */
    private function handleSubscriptionDisabled(array $data): void
    {
        Log::info('PayStack subscription disabled', ['code' => $data['subscription_code'] ?? null]);
    }

    /**
     * Handle invoice created (PayStack recurring billing).
     */
    private function handleInvoiceCreated(array $data): void
    {
        Log::info('PayStack invoice created', ['id' => $data['id'] ?? null]);
    }

    /**
     * Handle invoice updated.
     */
    private function handleInvoiceUpdated(array $data): void
    {
        Log::info('PayStack invoice updated', ['id' => $data['id'] ?? null]);
    }

    /**
     * Handle payment callback (redirect from PayStack).
     */
    public function callback(Request $request)
    {
        $reference = $request->get('reference') ?? $request->get('trxref');

        if (!$reference) {
            return redirect()->route('billing.index')
                ->with('error', 'Payment reference not found.');
        }

        // Verify transaction
        $result = $this->payStackService->verifyTransaction($reference);

        if ($result['success']) {
            return redirect()->route('billing.index')
                ->with('success', 'Payment successful! Your module is now active.');
        } else {
            return redirect()->route('billing.index')
                ->with('error', 'Payment verification failed: ' . $result['message']);
        }
    }

    // ─── Private Methods ──────────────────────────────────────────────────────

    private function activateModule(ModuleInvoiceItem $invoiceItem): void
    {
        $subscription = $invoiceItem->subscription;
        
        if (!$subscription) {
            return;
        }

        // Update subscription status
        $subscription->update([
            'status' => 'active',
            'installed_at' => now(),
        ]);

        // Enable module
        TenantModule::updateOrCreate(
            [
                'tenant_id' => $subscription->tenant_id,
                'module' => $subscription->module_key,
            ],
            [
                'subscription_id' => $subscription->id,
                'is_enabled' => true,
                'enabled_at' => now(),
            ]
        );

        // Flush cache
        app(\App\Services\ModuleService::class)->flushCache(
            $subscription->tenant_id,
            $subscription->module_key
        );

        Log::info('Module activated after payment', [
            'subscription_id' => $subscription->id,
            'module' => $subscription->module_key,
        ]);
    }
}
