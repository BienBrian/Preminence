<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Services\ModuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SubscriptionWebhookController extends Controller
{
    public function __construct(private ModuleService $moduleService)
    {
    }

    /**
     * Handle M-Pesa STK Push callback for subscription payments.
     */
    public function handleMpesaCallback(Request $request)
    {
        $payload = $request->all();
        
        Log::info('Subscription payment callback received', $payload);

        // Validate the callback
        if (!isset($payload['Body']['stkCallback'])) {
            return response()->json(['error' => 'Invalid callback'], 400);
        }

        $callback = $payload['Body']['stkCallback'];
        $resultCode = $callback['ResultCode'];
        $merchantRequestId = $callback['MerchantRequestID'];
        
        // Find the pending subscription by merchant request ID
        $subscription = Subscription::where('metadata->mpesa_merchant_request_id', $merchantRequestId)
            ->where('status', 'pending')
            ->first();

        if (!$subscription) {
            Log::error('Subscription not found for merchant request', ['merchant_request_id' => $merchantRequestId]);
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        if ($resultCode === 0) {
            // Payment successful
            $this->processSuccessfulPayment($subscription, $callback);
        } else {
            // Payment failed
            $this->processFailedPayment($subscription, $callback);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Process a successful subscription payment.
     */
    private function processSuccessfulPayment(Subscription $subscription, array $callback): void
    {
        $callbackMetadata = $callback['CallbackMetadata']['Item'] ?? [];
        $amount = null;
        $mpesaReceiptNumber = null;
        $transactionDate = null;
        $phoneNumber = null;

        foreach ($callbackMetadata as $item) {
            switch ($item['Name']) {
                case 'Amount':
                    $amount = $item['Value'];
                    break;
                case 'MpesaReceiptNumber':
                    $mpesaReceiptNumber = $item['Value'];
                    break;
                case 'TransactionDate':
                    $transactionDate = $item['Value'];
                    break;
                case 'PhoneNumber':
                    $phoneNumber = $item['Value'];
                    break;
            }
        }

        // Update subscription
        $subscription->update([
            'status' => 'active',
            'paid_at' => now(),
            'amount_paid' => $amount,
            'metadata' => array_merge($subscription->metadata ?? [], [
                'mpesa_receipt_number' => $mpesaReceiptNumber,
                'mpesa_transaction_date' => $transactionDate,
                'mpesa_phone_number' => $phoneNumber,
                'mpesa_callback' => $callback,
            ]),
        ]);

        // Update tenant
        $tenant = $subscription->tenant;
        $plan = $subscription->plan;
        
        $tenant->update([
            'status' => 'active',
            'plan_id' => $plan->id,
            'subscription_ends_at' => now()->add(
                $plan->billing_period === 'yearly' ? '1 year' : '1 month'
            ),
        ]);

        // Enable modules for new plan
        $this->syncModulesForPlan($tenant, $plan);

        // Flush module cache
        $this->moduleService->flushCache($tenant->id);

        // Log the action
        PlatformAuditLog::record(
            action: 'subscription.paid',
            details: [
                'subscription_id' => $subscription->id,
                'amount' => $amount,
                'mpesa_receipt' => $mpesaReceiptNumber,
                'plan_id' => $plan->id,
            ],
            tenantId: $tenant->id
        );

        Log::info('Subscription payment processed successfully', [
            'subscription_id' => $subscription->id,
            'tenant_id' => $tenant->id,
            'amount' => $amount,
        ]);
    }

    /**
     * Process a failed subscription payment.
     */
    private function processFailedPayment(Subscription $subscription, array $callback): void
    {
        $resultDesc = $callback['ResultDesc'] ?? 'Unknown error';

        $subscription->update([
            'status' => 'failed',
            'metadata' => array_merge($subscription->metadata ?? [], [
                'failure_reason' => $resultDesc,
                'mpesa_callback' => $callback,
            ]),
        ]);

        PlatformAuditLog::record(
            action: 'subscription.payment_failed',
            details: [
                'subscription_id' => $subscription->id,
                'reason' => $resultDesc,
            ],
            tenantId: $subscription->tenant_id
        );

        Log::warning('Subscription payment failed', [
            'subscription_id' => $subscription->id,
            'reason' => $resultDesc,
        ]);
    }

    /**
     * Sync enabled modules based on the plan.
     */
    private function syncModulesForPlan(Tenant $tenant, $plan): void
    {
        $planModules = $plan->modules ?? [];

        // Disable all modules first
        TenantModule::where('tenant_id', $tenant->id)
            ->where('override_by_admin', false)
            ->update(['is_enabled' => false, 'disabled_at' => now()]);

        // Enable modules from plan
        foreach ($planModules as $module => $enabled) {
            if ($enabled) {
                TenantModule::updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'module' => $module,
                    ],
                    [
                        'is_enabled' => true,
                        'enabled_at' => now(),
                    ]
                );
            }
        }
    }
}
