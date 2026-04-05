<?php

namespace App\Services\Modules;

use App\DTOs\ModulePrice;
use App\Models\Plan;
use App\Models\Tenant;
use App\Repositories\Contracts\ModuleRepositoryInterface;

/**
 * Service for calculating module pricing.
 * 
 * Handles pricing calculations including proration, discounts, and totals.
 */
class PricingCalculator
{
    private ModuleRepositoryInterface $moduleRepository;

    public function __construct(ModuleRepositoryInterface $moduleRepository)
    {
        $this->moduleRepository = $moduleRepository;
    }

    /**
     * Calculate the price for a module for a specific tenant.
     * 
     * Considers plan overrides and module defaults.
     *
     * @param string $moduleKey The module identifier
     * @param Tenant $tenant The target tenant
     * @param string $billingCycle 'monthly' or 'yearly'
     * @return ModulePrice
     */
    public function calculate(string $moduleKey, Tenant $tenant, string $billingCycle = 'monthly'): ModulePrice
    {
        $module = $this->moduleRepository->findByKey($moduleKey);
        
        if (!$module) {
            return new ModulePrice();
        }

        // Core modules are always free
        if ($module->isCore()) {
            return new ModulePrice(
                monthly: null,
                yearly: null,
                setupFee: 0,
                currency: 'KES',
            );
        }

        // Check plan for overrides
        $planModule = $this->getPlanModule($moduleKey, $tenant->plan);
        
        if ($planModule?->is_included) {
            return new ModulePrice(
                monthly: 0,
                yearly: 0,
                setupFee: 0,
                currency: 'KES',
            );
        }

        // Get base price from plan override or module default
        $monthly = $planModule?->price_monthly_override ?? $module->price_monthly;
        $yearly = $planModule?->price_yearly_override ?? $module->price_yearly;
        $setupFee = $planModule?->setup_fee_override ?? $module->setup_fee;

        // Calculate yearly savings
        $yearlySavings = null;
        if ($monthly && $yearly) {
            $monthlyCost = $monthly * 12;
            $yearlySavings = (int) round((($monthlyCost - $yearly) / $monthlyCost) * 100);
        }

        return new ModulePrice(
            monthly: $monthly,
            yearly: $yearly,
            setupFee: $setupFee ?? 0,
            yearlySavingsPercent: $yearlySavings,
            currency: 'KES',
        );
    }

    /**
     * Calculate prorated price for mid-cycle upgrades.
     * 
     * Calculates the amount to charge when installing mid-billing-cycle.
     *
     * @param string $moduleKey The target module
     * @param Tenant $tenant The target tenant
     * @param string $billingCycle 'monthly' or 'yearly'
     * @param \Carbon\Carbon|null $startDate When the module becomes active
     * @return ModulePrice
     */
    public function calculateProrated(
        string $moduleKey, 
        Tenant $tenant, 
        string $billingCycle,
        ?\Carbon\Carbon $startDate = null
    ): ModulePrice {
        $basePrice = $this->calculate($moduleKey, $tenant, $billingCycle);
        $startDate = $startDate ?? now();
        
        // Get current billing period info
        $subscription = $tenant->activeSubscription();
        if (!$subscription || !$subscription->period_ends_at) {
            // No active subscription, use full price
            return $basePrice;
        }

        $periodStart = $subscription->period_starts_at ?? now();
        $periodEnd = $subscription->period_ends_at;
        
        // Ensure we're working with Carbon instances
        if (!$periodStart instanceof \Carbon\Carbon) {
            $periodStart = \Carbon\Carbon::parse($periodStart);
        }
        if (!$periodEnd instanceof \Carbon\Carbon) {
            $periodEnd = \Carbon\Carbon::parse($periodEnd);
        }

        $daysRemaining = now()->diffInDays($periodEnd);
        $totalDays = $periodStart->diffInDays($periodEnd);
        
        if ($totalDays <= 0) {
            return $basePrice;
        }

        $prorationFactor = $daysRemaining / $totalDays;
        
        $price = $basePrice->getPrice($billingCycle);
        $proratedPrice = $price ? round($price * $prorationFactor, 2) : null;

        return new ModulePrice(
            monthly: $billingCycle === 'monthly' ? $proratedPrice : null,
            yearly: $billingCycle === 'yearly' ? $proratedPrice : null,
            setupFee: $basePrice->setupFee,
            currency: $basePrice->currency,
        );
    }

    /**
     * Calculate total monthly cost for all tenant add-ons.
     * 
     * @param Tenant $tenant The target tenant
     * @return array Cost breakdown
     */
    public function calculateTotalAddonCost(Tenant $tenant): array
    {
        $subscriptions = $tenant->moduleSubscriptions()
            ->whereIn('billing_type', ['addon_monthly', 'addon_yearly'])
            ->where('status', 'active')
            ->get();

        $monthly = 0;
        $yearly = 0;
        $breakdown = [];

        foreach ($subscriptions as $sub) {
            if ($sub->billing_type === 'addon_yearly') {
                $yearly += $sub->price ?? 0;
                $monthlyEquivalent = ($sub->price ?? 0) / 12;
            } else {
                $monthly += $sub->price ?? 0;
                $monthlyEquivalent = $sub->price ?? 0;
            }

            $breakdown[] = [
                'module' => $sub->module_key,
                'name' => $this->moduleRepository->findByKey($sub->module_key)?->name ?? $sub->module_key,
                'monthly_equivalent' => round($monthlyEquivalent, 2),
                'billing_type' => $sub->billing_type,
                'actual_price' => $sub->price,
            ];
        }

        return [
            'monthly' => round($monthly, 2),
            'yearly' => round($yearly, 2),
            'total_monthly_equivalent' => round($monthly + ($yearly / 12), 2),
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Calculate cancellation refund amount.
     * 
     * Determines prorated refund when uninstalling mid-cycle.
     *
     * @param TenantModuleSubscription $subscription The subscription to refund
     * @return float|null Refund amount or null if no refund applicable
     */
    public function calculateCancellationRefund(\App\Models\TenantModuleSubscription $subscription): ?float
    {
        if (!$subscription->isRecurring()) {
            return null;
        }

        if (!$subscription->last_billed_at || !$subscription->next_billing_at) {
            return null;
        }

        $periodStart = $subscription->last_billed_at;
        $periodEnd = $subscription->next_billing_at;
        
        $daysUsed = $periodStart->diffInDays(now());
        $totalDays = $periodStart->diffInDays($periodEnd);

        if ($totalDays <= 0) {
            return null;
        }

        $unusedDays = max(0, $totalDays - $daysUsed);
        $dailyRate = ($subscription->price ?? 0) / $totalDays;

        return round($unusedDays * $dailyRate, 2);
    }

    /**
     * Calculate upgrade cost when changing plans.
     * 
     * @param Tenant $tenant The target tenant
     * @param Plan $newPlan The plan to upgrade to
     * @return array Cost details
     */
    public function calculatePlanUpgrade(Tenant $tenant, Plan $newPlan): array
    {
        $currentPlan = $tenant->plan;
        
        if (!$currentPlan) {
            return [
                'prorated_plan_cost' => $newPlan->price,
                'additional_modules' => [],
                'total' => $newPlan->price,
            ];
        }

        // Calculate prorated plan difference
        $planPriceDiff = $newPlan->price - $currentPlan->price;
        
        // Get modules that will be newly included
        $currentModules = $currentPlan->planModules()->pluck('module_key');
        $newModules = $newPlan->planModules()
            ->where('is_included', true)
            ->whereNotIn('module_key', $currentModules)
            ->get();

        $additionalModules = [];
        foreach ($newModules as $planModule) {
            $additionalModules[] = [
                'module' => $planModule->module_key,
                'name' => $this->moduleRepository->findByKey($planModule->module_key)?->name,
                'savings' => $this->calculate($planModule->module_key, $tenant, 'monthly')->monthly ?? 0,
            ];
        }

        return [
            'current_plan' => $currentPlan->name,
            'new_plan' => $newPlan->name,
            'prorated_plan_cost' => max(0, $planPriceDiff),
            'additional_modules' => $additionalModules,
            'total_module_savings' => collect($additionalModules)->sum('savings'),
            'effective_date' => now()->toDateString(),
        ];
    }

    /**
     * Get plan module configuration.
     */
    private function getPlanModule(string $moduleKey, ?Plan $plan): ?\App\Models\PlanModule
    {
        if (!$plan) {
            return null;
        }

        return $plan->planModules()
            ->where('module_key', $moduleKey)
            ->first();
    }
}
