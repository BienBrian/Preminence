<?php

namespace App\Services\Payment;

use App\Models\ModuleInvoiceItem;
use App\Models\Tenant;
use App\Models\TenantModuleSubscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PayStack Payment Gateway Service
 * 
 * Handles payment initialization, verification, refunds, and webhooks.
 * @see https://paystack.com/docs/payments/accept-payments/
 */
class PayStackService
{
    private string $baseUrl;
    private string $secretKey;
    private string $publicKey;
    private bool $isLive;

    public function __construct()
    {
        $this->isLive = config('paystack.is_live', false);
        $this->baseUrl = config('paystack.base_url', 'https://api.paystack.co');
        $this->secretKey = config('paystack.secret_key');
        $this->publicKey = config('paystack.public_key');
    }

    /**
     * Initialize a payment transaction.
     * 
     * @param array $data Payment data
     * @return array Response with authorization_url, reference, etc.
     */
    public function initializePayment(array $data): array
    {
        $payload = [
            'email' => $data['email'],
            'amount' => $this->toKobo($data['amount']), // Convert to kobo/cents
            'reference' => $data['reference'] ?? $this->generateReference(),
            'callback_url' => $data['callback_url'] ?? $this->getCallbackUrl(),
            'currency' => $data['currency'] ?? config('paystack.currency', 'KES'),
            'channels' => $data['channels'] ?? config('paystack.channels', ['card', 'bank', 'mobile_money']),
            'metadata' => $data['metadata'] ?? [],
        ];

        // Add optional fields
        if (!empty($data['plan'])) {
            $payload['plan'] = $data['plan'];
        }
        if (!empty($data['subaccount'])) {
            $payload['subaccount'] = $data['subaccount'];
        }
        if (!empty($data['split_code'])) {
            $payload['split_code'] = $data['split_code'];
        }
        if (isset($data['transaction_charge'])) {
            $payload['transaction_charge'] = $this->toKobo($data['transaction_charge']);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/transaction/initialize", $payload);

            if ($response->successful() && $response->json('status')) {
                $result = $response->json('data');
                
                Log::info('PayStack payment initialized', [
                    'reference' => $result['reference'],
                    'amount' => $data['amount'],
                    'email' => $data['email'],
                ]);

                return [
                    'success' => true,
                    'authorization_url' => $result['authorization_url'],
                    'access_code' => $result['access_code'],
                    'reference' => $result['reference'],
                ];
            }

            Log::error('PayStack initialization failed', [
                'response' => $response->json(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'message' => $response->json('message', 'Payment initialization failed'),
            ];

        } catch (\Exception $e) {
            Log::error('PayStack initialization error', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'message' => 'Payment service temporarily unavailable',
            ];
        }
    }

    /**
     * Verify a transaction.
     * 
     * @param string $reference Transaction reference
     * @return array Transaction details
     */
    public function verifyTransaction(string $reference): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
            ])->get("{$this->baseUrl}/transaction/verify/{$reference}");

            if ($response->successful()) {
                $result = $response->json();

                if ($result['status'] && $result['data']['status'] === 'success') {
                    Log::info('PayStack transaction verified', [
                        'reference' => $reference,
                        'amount' => $result['data']['amount'],
                    ]);

                    return [
                        'success' => true,
                        'data' => $result['data'],
                        'gateway_response' => $result['data']['gateway_response'] ?? null,
                        'channel' => $result['data']['channel'] ?? null,
                    ];
                }

                return [
                    'success' => false,
                    'status' => $result['data']['status'] ?? 'unknown',
                    'message' => $result['message'] ?? 'Transaction verification failed',
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to verify transaction',
            ];

        } catch (\Exception $e) {
            Log::error('PayStack verification error', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Verification service temporarily unavailable',
            ];
        }
    }

    /**
     * Process a refund.
     * 
     * @param string $reference Original transaction reference
     * @param float|null $amount Amount to refund (null = full refund)
     * @param string|null $reason Reason for refund
     * @return array Refund result
     */
    public function refundTransaction(string $reference, ?float $amount = null, ?string $reason = null): array
    {
        $payload = [
            'transaction' => $reference,
        ];

        if ($amount !== null) {
            $payload['amount'] = $this->toKobo($amount);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/refund", $payload);

            if ($response->successful() && $response->json('status')) {
                $result = $response->json('data');

                Log::info('PayStack refund processed', [
                    'reference' => $reference,
                    'refund_id' => $result['id'],
                    'amount' => $amount,
                    'reason' => $reason,
                ]);

                return [
                    'success' => true,
                    'refund_id' => $result['id'],
                    'status' => $result['status'],
                    'amount' => $this->fromKobo($result['amount']),
                    'data' => $result,
                ];
            }

            Log::error('PayStack refund failed', [
                'reference' => $reference,
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'message' => $response->json('message', 'Refund failed'),
            ];

        } catch (\Exception $e) {
            Log::error('PayStack refund error', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Refund service temporarily unavailable',
            ];
        }
    }

    /**
     * Create a payment page (for direct payments).
     * 
     * @param array $data Page configuration
     * @return array Page details including slug
     */
    public function createPaymentPage(array $data): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/page", [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'amount' => isset($data['amount']) ? $this->toKobo($data['amount']) : null,
                'slug' => $data['slug'] ?? null,
                'metadata' => $data['metadata'] ?? [],
            ]);

            if ($response->successful() && $response->json('status')) {
                return [
                    'success' => true,
                    'data' => $response->json('data'),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('message', 'Failed to create payment page'),
            ];

        } catch (\Exception $e) {
            Log::error('PayStack create page error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Service temporarily unavailable',
            ];
        }
    }

    /**
     * Fetch transaction timeline.
     * 
     * @param string $reference Transaction reference
     * @return array Timeline data
     */
    public function fetchTransactionTimeline(string $reference): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
            ])->get("{$this->baseUrl}/transaction/timeline/{$reference}");

            if ($response->successful() && $response->json('status')) {
                return [
                    'success' => true,
                    'timeline' => $response->json('data'),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('message', 'Failed to fetch timeline'),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Service temporarily unavailable',
            ];
        }
    }

    /**
     * Verify webhook signature.
     * 
     * @param string $payload Raw request body
     * @param string $signature X-Paystack-Signature header
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = config('paystack.webhook.secret');
        
        if (empty($secret)) {
            Log::warning('PayStack webhook secret not configured');
            return false;
        }

        $computed = hash_hmac('sha512', $payload, $secret);
        
        return hash_equals($computed, $signature);
    }

    /**
     * Initialize payment for module invoice item.
     * 
     * @param ModuleInvoiceItem $invoiceItem
     * @param Tenant $tenant
     * @param string $email
     * @return array
     */
    public function initializeModulePayment(
        ModuleInvoiceItem $invoiceItem,
        Tenant $tenant,
        string $email
    ): array {
        $reference = 'MOD_' . $invoiceItem->id . '_' . time();
        
        return $this->initializePayment([
            'email' => $email,
            'amount' => $invoiceItem->total_amount,
            'reference' => $reference,
            'currency' => $invoiceItem->currency,
            'metadata' => [
                'invoice_item_id' => $invoiceItem->id,
                'tenant_id' => $tenant->id,
                'module_key' => $invoiceItem->module_key,
                'custom_fields' => [
                    [
                        'display_name' => 'Module',
                        'variable_name' => 'module_name',
                        'value' => $invoiceItem->module?->name ?? $invoiceItem->module_key,
                    ],
                    [
                        'display_name' => 'Tenant',
                        'variable_name' => 'tenant_name',
                        'value' => $tenant->name,
                    ],
                ],
            ],
        ]);
    }

    // ─── Private Methods ──────────────────────────────────────────────────────

    private function toKobo(float $amount): int
    {
        return (int) round($amount * 100);
    }

    private function fromKobo(int $amount): float
    {
        return $amount / 100;
    }

    private function generateReference(): string
    {
        $prefix = config('paystack.transaction.reference_prefix', 'PISTI_');
        return $prefix . uniqid() . '_' . time();
    }

    private function getCallbackUrl(): string
    {
        $customUrl = config('paystack.transaction.callback_url');
        
        if ($customUrl) {
            return url($customUrl);
        }
        
        return route('payments.callback');
    }

    /**
     * Get the public key for client-side use.
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * Check if in live mode.
     */
    public function isLive(): bool
    {
        return $this->isLive;
    }
}
