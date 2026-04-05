<?php

namespace App\Services\Modules;

use App\Models\Tenant;
use App\Models\TenantModuleSubscription;
use Carbon\Carbon;

/**
 * Service for calculating prorated amounts for module billing.
 * 
 * Handles mid-cycle installations, cancellations, and billing cycle changes.
 */
class ProrationCalculator
{
    /**
     * Calculate prorated amount for module installation.
     * 
     * @param TenantModuleSubscription $subscription
     * @param string $billingCycle 'monthly' or 'yearly'
     * @return array Proration details
     */
    public function calculateInstallProration(
        TenantModuleSubscription $subscription,
        string $billingCycle
    ): array {
        $tenant = $subscription->tenant;
        $fullPrice = $subscription->price;
        $setupFee = $this->getSetupFee($subscription);
        
        // Get tenant's current billing period
        $tenantSubscription = $tenant->activeSubscription();
        
        if (!$tenantSubscription || !$tenantSubscription->period_ends_at) {
            // No active subscription, charge full price
            return [
                'full_price' => $fullPrice,
                'setup_fee' => $setupFee,
                'prorated_amount' => $fullPrice,
                'total_due' => $fullPrice + $setupFee,
                'is_prorated' => false,
                'period_start' => now(),
                'period_end' => $billingCycle === 'yearly' ? now()->addYear() : now()->addMonth(),
                'days_remaining' => $billingCycle === 'yearly' ? 365 : 30,
                'daily_rate' => $billingCycle === 'yearly' ? $fullPrice / 365 : $fullPrice / 30,
                'proration_factor' => 1.0,
            ];
        }

        $periodStart = $tenantSubscription->period_starts_at 
            ? Carbon::parse($tenantSubscription->period_starts_at) 
            : now();
        $periodEnd = Carbon::parse($tenantSubscription->period_ends_at);
        
        $now = now();
        $daysRemaining = (int) $now->diffInDays($periodEnd, false);
        $totalDays = (int) $periodStart->diffInDays($periodEnd);
        
        if ($daysRemaining <= 0 || $totalDays <= 0) {
            // Period ended or invalid, charge full price
            return [
                'full_price' => $fullPrice,
                'setup_fee' => $setupFee,
                'prorated_amount' => $fullPrice,
                'total_due' => $fullPrice + $setupFee,
                'is_prorated' => false,
                'period_start' => $now,
                'period_end' => $billingCycle === 'yearly' ? $now->copy()->addYear() : $now->copy()->addMonth(),
                'days_remaining' => $billingCycle === 'yearly' ? 365 : 30,
                'daily_rate' => $billingCycle === 'yearly' ? $fullPrice / 365 : $fullPrice / 30,
                'proration_factor' => 1.0,
            ];
        }

        $prorationFactor = $daysRemaining / $totalDays;
        $dailyRate = $fullPrice / $totalDays;
        $proratedAmount = round($dailyRate * $daysRemaining, 2);

        return [
            'full_price' => $fullPrice,
            'setup_fee' => $setupFee,
            'prorated_amount' => $proratedAmount,
            'total_due' => $proratedAmount + $setupFee,
            'is_prorated' => true,
            'period_start' => $now,
            'period_end' => $periodEnd,
            'days_remaining' => $daysRemaining,
            'total_period_days' => $totalDays,
            'daily_rate' => round($dailyRate, 4),
            'proration_factor' => round($prorationFactor, 4),
            'savings' => round($fullPrice - $proratedAmount, 2),
        ];
    }

    /**
     * Calculate prorated refund for cancellation.
     * 
     * @param TenantModuleSubscription $subscription
     * @return array Refund details
     */
    public function calculateCancellationRefund(TenantModuleSubscription $subscription): array
    {
        if (!$subscription->isRecurring()) {
            return [
                'refundable' => false,
                'refund_amount' => 0,
                'reason' => 'Not a recurring subscription',
            ];
        }

        $lastBilled = $subscription->last_billed_at;
        $nextBilling = $subscription->next_billing_at;

        if (!$lastBilled || !$nextBilling) {
            return [
                'refundable' => false,
                'refund_amount' => 0,
                'reason' => 'No billing period found',
            ];
        }

        $now = now();
        
        // If already past next billing, no refund
        if ($now->greaterThanOrEqualTo($nextBilling)) {
            return [
                'refundable' => false,
                'refund_amount' => 0,
                'reason' => 'Billing period has ended',
            ];
        }

        $totalDays = (int) $lastBilled->diffInDays($nextBilling);
        $daysUsed = (int) $lastBilled->diffInDays($now);
        $daysRemaining = max(0, $totalDays - $daysUsed);

        if ($daysRemaining <= 0) {
            return [
                'refundable' => false,
                'refund_amount' => 0,
                'reason' => 'No unused days remaining',
            ];
        }

        $dailyRate = $subscription->price / $totalDays;
        $refundAmount = round($dailyRate * $daysRemaining, 2);

        return [
            'refundable' => true,
            'refund_amount' => $refundAmount,
            'period_start' => $lastBilled,
            'period_end' => $nextBilling,
            'days_used' => $daysUsed,
            'days_remaining' => $daysRemaining,
            'total_days' => $totalDays,
            'daily_rate' => round($dailyRate, 4),
            'unused_percentage' => round(($daysRemaining / $totalDays) * 100, 2),
        ];
    }

    /**
     * Calculate price difference when changing billing cycles.
     * 
     * @param TenantModuleSubscription $subscription
     * @param string $newCycle 'monthly' or 'yearly'
     * @return array Adjustment details
     */
    public function calculateBillingCycleChange(
        TenantModuleSubscription $subscription,
        string $newCycle
    ): array {
        $module = $subscription->module;
        
        if (!$module) {
            return [
                'can_change' => false,
                'reason' => 'Module not found',
            ];
        }

        $currentCycle = $subscription->billing_type === 'addon_yearly' ? 'yearly' : 'monthly';
        
        if ($currentCycle === $newCycle) {
            return [
                'can_change' => true,
                'difference' => 0,
                'reason' => 'No change needed',
            ];
        }

        // Get new price
        $newPrice = $newCycle === 'yearly' ? $module->price_yearly : $module->price_monthly;
        
        if ($newPrice === null) {
            return [
                'can_change' => false,
                'reason' => "{$newCycle} billing not available for this module",
            ];
        }

        $currentPrice = $subscription->price;
        
        // Calculate remaining value in current period
        $now = now();
        $lastBilled = $subscription->last_billed_at ?? $subscription->installed_at;
        $nextBilling = $subscription->next_billing_at;
        
        if ($lastBilled && $nextBilling && $now->lessThan($nextBilling)) {
            $totalDays = (int) $lastBilled->diffInDays($nextBilling);
            $daysUsed = (int) $lastBilled->diffInDays($now);
            $daysRemaining = max(0, $totalDays - $daysUsed);
            
            $currentDailyRate = $currentPrice / $totalDays;
            $remainingValue = $currentDailyRate * $daysRemaining;
            
            // Calculate new period value
            $newPeriodDays = $newCycle === 'yearly' ? 365 : 30;
            $newDailyRate = $newPrice / $newPeriodDays;
            $newPeriodValue = $newDailyRate * $daysRemaining;
            
            $difference = round($newPeriodValue - $remainingValue, 2);
        } else {
            // No active period, just calculate difference
            $difference = round($newPrice - $currentPrice, 2);
        }

        return [
            'can_change' => true,
            'current_cycle' => $currentCycle,
            'new_cycle' => $newCycle,
            'current_price' => $currentPrice,
            'new_price' => $newPrice,
            'difference' => $difference,
            'charge_or_credit' => $difference > 0 ? 'charge' : 'credit',
            'effective_date' => $now,
        ];
    }

    /**
     * Calculate prorated amount for plan upgrade.
     * 
     * @param Tenant $tenant
     * @param float $newPlanPrice
     * @param Carbon|null $effectiveDate
     * @return array Proration details
     */
    public function calculatePlanUpgradeProration(
        Tenant $tenant,
        float $newPlanPrice,
        ?Carbon $effectiveDate = null
    ): array {
        $effectiveDate = $effectiveDate ?? now();
        $currentSubscription = $tenant->activeSubscription();
        
        if (!$currentSubscription || !$currentSubscription->period_ends_at) {
            return [
                'can_upgrade' => true,
                'prorated_charge' => $newPlanPrice,
                'is_prorated' => false,
            ];
        }

        $periodEnd = Carbon::parse($currentSubscription->period_ends_at);
        
        if ($effectiveDate->greaterThanOrEqualTo($periodEnd)) {
            return [
                'can_upgrade' => true,
                'prorated_charge' => $newPlanPrice,
                'is_prorated' => false,
            ];
        }

        $currentPrice = $tenant->plan?->price ?? 0;
        $daysRemaining = (int) $effectiveDate->diffInDays($periodEnd);
        $totalDays = 30; // Assume monthly billing for plans
        
        $priceDifference = $newPlanPrice - $currentPrice;
        $dailyDifference = $priceDifference / $totalDays;
        $proratedCharge = round($dailyDifference * $daysRemaining, 2);

        return [
            'can_upgrade' => true,
            'current_plan_price' => $currentPrice,
            'new_plan_price' => $newPlanPrice,
            'price_difference' => $priceDifference,
            'prorated_charge' => $proratedCharge,
            'is_prorated' => true,
            'days_remaining' => $daysRemaining,
            'effective_date' => $effectiveDate,
            'next_billing_date' => $periodEnd,
        ];
    }

    /**
     * Format proration details for display.
     * 
     * @param array $proration
     * @return array Formatted details
     */
    public function formatForDisplay(array $proration): array
    {
        return [
            'amount_due' => 'KES ' . number_format($proration['total_due'] ?? $proration['prorated_charge'] ?? 0, 2),
            'setup_fee' => isset($proration['setup_fee']) && $proration['setup_fee'] > 0
                ? 'KES ' . number_format($proration['setup_fee'], 2)
                : null,
            'subscription_charge' => 'KES ' . number_format($proration['prorated_amount'] ?? $proration['prorated_charge'] ?? 0, 2),
            'period' => isset($proration['period_start']) && isset($proration['period_end'])
                ? $proration['period_start']->format('M d') . ' - ' . $proration['period_end']->format('M d, Y')
                : null,
            'days_remaining' => $proration['days_remaining'] ?? null,
            'savings' => isset($proration['savings']) && $proration['savings'] > 0
                ? 'KES ' . number_format($proration['savings'], 2)
                : null,
            'is_prorated' => $proration['is_prorated'] ?? false,
        ];
    }

    // ─── Private Methods ──────────────────────────────────────────────────────

    private function getSetupFee(TenantModuleSubscription $subscription): float
    {
        $module = $subscription->module;
        
        if (!$module) {
            return 0;
        }

        // Check if there's a plan override
        $planModule = $subscription->tenant->plan?->planModules()
            ->where('module_key', $subscription->module_key)
            ->first();

        return $planModule?->setup_fee_override ?? $module->setup_fee ?? 0;
    }
}
