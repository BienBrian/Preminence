<?php

namespace App\Services\Modules;

use App\Events\Modules\ModuleBilled;
use App\Events\Modules\ModuleBillingFailed;
use App\Models\TenantModuleSubscription;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling module subscription billing.
 * 
 * Processes recurring payments, handles failures with retries,
 * and manages trial conversions.
 */
class ModuleBillingService
{
    private PricingCalculator $pricingCalculator;

    public function __construct(PricingCalculator $pricingCalculator)
    {
        $this->pricingCalculator = $pricingCalculator;
    }

    /**
     * Process billing for a module subscription.
     * 
     * @param TenantModuleSubscription $subscription The subscription to bill
     * @return bool Success status
     */
    public function processBilling(TenantModuleSubscription $subscription): bool
    {
        if (!$subscription->isRecurring()) {
            return true;
        }

        // Check if due for billing
        if ($subscription->next_billing_at && $subscription->next_billing_at->isFuture()) {
            return true;
        }

        $tenant = $subscription->tenant;
        
        try {
            // Create invoice item
            $invoiceItem = $this->createInvoiceItem($subscription);
            
            // Process payment
            $paymentResult = $this->processPayment($subscription, $invoiceItem);
            
            if ($paymentResult['success']) {
                $subscription->update([
                    'last_billed_at' => now(),
                    'next_billing_at' => $this->calculateNextBilling($subscription),
                ]);
                
                event(new ModuleBilled($subscription, $invoiceItem));
                
                Log::info('Module billing successful', [
                    'subscription_id' => $subscription->id,
                    'amount' => $invoiceItem['amount'],
                ]);
                
                return true;
            } else {
                $this->handleBillingFailure($subscription, $paymentResult['error']);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Module billing failed', [
                'subscription_id' => $subscription->id,
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
            
            $this->handleBillingFailure($subscription, $e->getMessage());
            return false;
        }
    }

    /**
     * Process billing for all due subscriptions.
     * 
     * @return array Statistics on processed billings
     */
    public function processDueBillings(): array
    {
        $due = TenantModuleSubscription::pendingBilling()->get();
        
        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
        ];

        foreach ($due as $subscription) {
            $results['processed']++;
            
            if ($this->processBilling($subscription)) {
                $results['successful']++;
            } else {
                $results['failed']++;
            }
        }

        Log::info('Batch billing completed', $results);

        return $results;
    }

    /**
     * Handle trial conversion to paid.
     * 
     * @param TenantModuleSubscription $subscription The trial subscription
     * @return bool Success status
     */
    public function convertTrial(TenantModuleSubscription $subscription): bool
    {
        if (!$subscription->trialExpired()) {
            return false;
        }

        $price = $this->pricingCalculator->calculate(
            $subscription->module_key,
            $subscription->tenant,
            'monthly'
        );

        $subscription->update([
            'billing_type' => 'addon_monthly',
            'price' => $price->monthly,
            'trial_ends_at' => null,
            'next_billing_at' => now()->addMonth(),
        ]);

        Log::info('Trial converted to paid', [
            'subscription_id' => $subscription->id,
            'module' => $subscription->module_key,
        ]);

        // Process first payment
        return $this->processBilling($subscription);
    }

    /**
     * Process all expired trials.
     * 
     * @return array Statistics
     */
    public function processExpiredTrials(): array
    {
        $expired = TenantModuleSubscription::trialExpired()
            ->where('status', 'active')
            ->get();

        $results = [
            'processed' => 0,
            'converted' => 0,
            'suspended' => 0,
        ];

        foreach ($expired as $subscription) {
            $results['processed']++;
            
            if (config('modules.trial.auto_convert', true)) {
                if ($this->convertTrial($subscription)) {
                    $results['converted']++;
                } else {
                    $this->suspendForFailedTrial($subscription);
                    $results['suspended']++;
                }
            } else {
                $this->suspendForFailedTrial($subscription);
                $results['suspended']++;
            }
        }

        return $results;
    }

    /**
     * Calculate prorated charges for mid-cycle installation.
     * 
     * @param TenantModuleSubscription $subscription The new subscription
     * @return float The prorated amount
     */
    public function calculateProratedCharge(TenantModuleSubscription $subscription): float
    {
        if (!$subscription->isRecurring()) {
            return 0;
        }

        $tenantSubscription = $subscription->tenant->activeSubscription();
        if (!$tenantSubscription || !$tenantSubscription->period_ends_at) {
            return $subscription->price ?? 0;
        }

        $periodStart = now();
        $periodEnd = $tenantSubscription->period_ends_at;
        $totalDays = $periodStart->diffInDays($periodEnd);
        
        if ($totalDays <= 0) {
            return $subscription->price ?? 0;
        }

        $monthlyPrice = $subscription->price ?? 0;
        $dailyRate = $monthlyPrice / 30; // Assume 30-day month

        return round($dailyRate * $totalDays, 2);
    }

    /**
     * Process refund for cancellation.
     * 
     * @param TenantModuleSubscription $subscription The subscription being cancelled
     * @return array Refund details
     */
    public function processRefund(TenantModuleSubscription $subscription): array
    {
        $refundAmount = $this->pricingCalculator->calculateCancellationRefund($subscription);
        
        if (!$refundAmount || $refundAmount <= 0) {
            return [
                'success' => true,
                'refunded' => false,
                'amount' => 0,
                'reason' => 'No refund applicable',
            ];
        }

        // Process refund through payment gateway
        $result = $this->processRefundPayment($subscription, $refundAmount);

        Log::info('Refund processed', [
            'subscription_id' => $subscription->id,
            'amount' => $refundAmount,
            'success' => $result['success'],
        ]);

        return [
            'success' => $result['success'],
            'refunded' => $result['success'],
            'amount' => $refundAmount,
            'transaction_id' => $result['transaction_id'] ?? null,
        ];
    }

    // ─── Private Methods ──────────────────────────────────────────────────────

    /**
     * Create invoice item for billing.
     */
    private function createInvoiceItem(TenantModuleSubscription $subscription): array
    {
        $module = $subscription->module;
        
        return [
            'description' => "Module: " . ($module?->name ?? $subscription->module_key),
            'module_key' => $subscription->module_key,
            'amount' => $subscription->price,
            'currency' => $subscription->currency,
            'period_start' => $subscription->last_billed_at ?? $subscription->installed_at,
            'period_end' => $subscription->next_billing_at,
            'billing_type' => $subscription->billing_type,
        ];
    }

    /**
     * Process payment through gateway.
     * 
     * This is a placeholder - integrate with actual payment service.
     */
    private function processPayment(
        TenantModuleSubscription $subscription, 
        array $invoiceItem
    ): array {
        // TODO: Integrate with actual payment gateway (M-Pesa, Stripe, etc.)
        
        // Placeholder implementation
        return [
            'success' => true,
            'transaction_id' => 'mock_' . uniqid(),
            'message' => 'Payment processed successfully',
        ];
    }

    /**
     * Process refund through gateway.
     */
    private function processRefundPayment(
        TenantModuleSubscription $subscription,
        float $amount
    ): array {
        // TODO: Integrate with actual payment gateway
        
        return [
            'success' => true,
            'transaction_id' => 'refund_' . uniqid(),
        ];
    }

    /**
     * Handle billing failure.
     */
    private function handleBillingFailure(
        TenantModuleSubscription $subscription, 
        string $error
    ): void {
        $retryCount = ($subscription->metadata['billing_retries'] ?? 0) + 1;
        $maxRetries = config('modules.billing.retry_attempts', 3);

        if ($retryCount >= $maxRetries) {
            // Max retries reached, suspend
            $subscription->update([
                'status' => 'suspended',
                'suspended_at' => now(),
                'suspension_reason' => 'billing_failed: ' . $error,
            ]);

            // Disable module
            \App\Models\TenantModule::where('tenant_id', $subscription->tenant_id)
                ->where('module', $subscription->module_key)
                ->update(['is_enabled' => false]);

            event(new ModuleBillingFailed($subscription, $error));
        } else {
            // Schedule retry
            $subscription->update([
                'metadata' => array_merge(
                    $subscription->metadata ?? [],
                    ['billing_retries' => $retryCount]
                ),
                'next_billing_at' => now()->addHours(
                    config('modules.billing.retry_delay_hours', 24)
                ),
            ]);
        }

        Log::warning('Billing failure handled', [
            'subscription_id' => $subscription->id,
            'retry' => $retryCount,
            'max_retries' => $maxRetries,
            'suspended' => $retryCount >= $maxRetries,
        ]);
    }

    /**
     * Suspend subscription for failed trial conversion.
     */
    private function suspendForFailedTrial(TenantModuleSubscription $subscription): void
    {
        $subscription->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspension_reason' => 'trial_expired_no_payment',
        ]);

        // Disable module
        \App\Models\TenantModule::where('tenant_id', $subscription->tenant_id)
            ->where('module', $subscription->module_key)
            ->update(['is_enabled' => false]);

        event(new ModuleBillingFailed($subscription, 'Trial expired without payment method'));
    }

    /**
     * Calculate next billing date.
     */
    private function calculateNextBilling(TenantModuleSubscription $subscription): \Carbon\Carbon
    {
        return $subscription->billing_type === 'addon_yearly'
            ? now()->addYear()
            : now()->addMonth();
    }
}
