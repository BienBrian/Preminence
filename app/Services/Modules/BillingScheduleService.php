<?php

namespace App\Services\Modules;

use App\Models\TenantModuleSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Service for managing billing schedules and calculating next billing dates.
 * 
 * Handles different billing cycles, grace periods, and billing alignment.
 */
class BillingScheduleService
{
    /**
     * Calculate the next billing date for a subscription.
     * 
     * @param TenantModuleSubscription $subscription
     * @param Carbon|null $fromDate
     * @return Carbon
     */
    public function calculateNextBillingDate(
        TenantModuleSubscription $subscription,
        ?Carbon $fromDate = null
    ): Carbon {
        $fromDate = $fromDate ?? now();
        
        return match($subscription->billing_type) {
            'addon_monthly' => $this->calculateMonthlyBilling($subscription, $fromDate),
            'addon_yearly' => $this->calculateYearlyBilling($subscription, $fromDate),
            'one_time' => $fromDate, // No recurring billing
            'plan_included' => $this->calculatePlanAlignedBilling($subscription, $fromDate),
            default => $fromDate->addMonth(),
        };
    }

    /**
     * Get upcoming billings for a tenant.
     * 
     * @param int $tenantId
     * @param int $daysAhead
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUpcomingBillings(int $tenantId, int $daysAhead = 30)
    {
        $cutoffDate = now()->addDays($daysAhead);
        
        return TenantModuleSubscription::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereIn('billing_type', ['addon_monthly', 'addon_yearly'])
            ->where('next_billing_at', '<=', $cutoffDate)
            ->where('next_billing_at', '>=', now())
            ->with('module')
            ->orderBy('next_billing_at')
            ->get();
    }

    /**
     * Get overdue billings across all tenants.
     * 
     * @param int $gracePeriodDays
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOverdueBillings(int $gracePeriodDays = 3)
    {
        $gracePeriodEnd = now()->subDays($gracePeriodDays);
        
        return TenantModuleSubscription::where('status', 'active')
            ->whereIn('billing_type', ['addon_monthly', 'addon_yearly'])
            ->where('next_billing_at', '<', $gracePeriodEnd)
            ->with(['tenant', 'module'])
            ->orderBy('next_billing_at')
            ->get();
    }

    /**
     * Align module billing to tenant's main subscription.
     * 
     * @param TenantModuleSubscription $subscription
     * @return array Alignment details
     */
    public function alignToTenantBilling(TenantModuleSubscription $subscription): array
    {
        $tenant = $subscription->tenant;
        $tenantSubscription = $tenant->activeSubscription();
        
        if (!$tenantSubscription || !$tenantSubscription->period_ends_at) {
            return [
                'can_align' => false,
                'reason' => 'No active tenant subscription found',
            ];
        }

        $currentNextBilling = $subscription->next_billing_at;
        $alignedDate = Carbon::parse($tenantSubscription->period_ends_at);
        
        // Calculate prorated charge for alignment period
        $daysUntilAlignment = $currentNextBilling 
            ? (int) $currentNextBilling->diffInDays($alignedDate)
            : 0;

        return [
            'can_align' => true,
            'current_next_billing' => $currentNextBilling,
            'aligned_date' => $alignedDate,
            'days_until_alignment' => $daysUntilAlignment,
            'will_extend_period' => $alignedDate->greaterThan($currentNextBilling ?? now()),
            'prorated_charge' => $daysUntilAlignment > 0 
                ? $this->calculateAlignmentCharge($subscription, $daysUntilAlignment)
                : 0,
        ];
    }

    /**
     * Schedule billing for a new subscription.
     * 
     * @param TenantModuleSubscription $subscription
     * @param bool $alignToTenant Whether to align with tenant's billing cycle
     * @return array Schedule details
     */
    public function scheduleNewSubscription(
        TenantModuleSubscription $subscription,
        bool $alignToTenant = true
    ): array {
        $module = $subscription->module;
        
        if (!$module) {
            return [
                'success' => false,
                'reason' => 'Module not found',
            ];
        }

        $trialDays = $this->calculateTrialDays($subscription, $module);
        $trialEndsAt = $trialDays > 0 ? now()->addDays($trialDays) : null;
        
        // Calculate first billing date
        if ($trialEndsAt) {
            $firstBilling = $trialEndsAt->copy();
        } else {
            $firstBilling = $this->calculateNextBillingDate($subscription);
        }
        
        // Align to tenant billing if requested
        if ($alignToTenant && $subscription->billing_type !== 'one_time') {
            $alignment = $this->alignToTenantBilling($subscription);
            if ($alignment['can_align'] && $alignment['aligned_date']->greaterThan($firstBilling)) {
                $firstBilling = $alignment['aligned_date'];
            }
        }

        return [
            'success' => true,
            'trial_days' => $trialDays,
            'trial_ends_at' => $trialEndsAt,
            'first_billing_date' => $firstBilling,
            'billing_type' => $subscription->billing_type,
        ];
    }

    /**
     * Get billing summary for a tenant.
     * 
     * @param int $tenantId
     * @return array Summary details
     */
    public function getTenantBillingSummary(int $tenantId): array
    {
        $subscriptions = TenantModuleSubscription::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereIn('billing_type', ['addon_monthly', 'addon_yearly'])
            ->get();

        $next7Days = $subscriptions->filter(
            fn($s) => $s->next_billing_at && $s->next_billing_at->diffInDays(now()) <= 7
        );
        
        $next30Days = $subscriptions->filter(
            fn($s) => $s->next_billing_at && $s->next_billing_at->diffInDays(now()) <= 30
        );

        return [
            'active_subscriptions' => $subscriptions->count(),
            'monthly_total' => $subscriptions
                ->where('billing_type', 'addon_monthly')
                ->sum('price'),
            'yearly_total' => $subscriptions
                ->where('billing_type', 'addon_yearly')
                ->sum('price'),
            'next_billing_date' => $subscriptions
                ->whereNotNull('next_billing_at')
                ->min('next_billing_at'),
            'due_next_7_days' => $next7Days->sum('price'),
            'due_next_30_days' => $next30Days->sum('price'),
            'subscriptions_due_soon' => $next7Days->map(fn($s) => [
                'module' => $s->module_key,
                'amount' => $s->price,
                'date' => $s->next_billing_at?->format('Y-m-d'),
            ]),
        ];
    }

    /**
     * Generate billing calendar for a date range.
     * 
     * @param int $tenantId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array Calendar events
     */
    public function generateBillingCalendar(
        int $tenantId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $subscriptions = TenantModuleSubscription::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereIn('billing_type', ['addon_monthly', 'addon_yearly'])
            ->get();

        $events = [];
        
        foreach ($subscriptions as $subscription) {
            $nextBilling = $subscription->next_billing_at;
            
            if (!$nextBilling) {
                continue;
            }
            
            // Add the next billing
            if ($nextBilling->between($startDate, $endDate)) {
                $events[] = [
                    'date' => $nextBilling->format('Y-m-d'),
                    'module' => $subscription->module_key,
                    'amount' => $subscription->price,
                    'type' => 'billing',
                ];
            }
            
            // For yearly subscriptions, add future billings in range
            if ($subscription->billing_type === 'addon_yearly') {
                $futureBilling = $nextBilling->copy()->addYear();
                while ($futureBilling->lessThanOrEqualTo($endDate)) {
                    if ($futureBilling->greaterThanOrEqualTo($startDate)) {
                        $events[] = [
                            'date' => $futureBilling->format('Y-m-d'),
                            'module' => $subscription->module_key,
                            'amount' => $subscription->price,
                            'type' => 'billing',
                        ];
                    }
                    $futureBilling->addYear();
                }
            }
            
            // For monthly subscriptions, add future billings in range
            if ($subscription->billing_type === 'addon_monthly') {
                $futureBilling = $nextBilling->copy()->addMonth();
                while ($futureBilling->lessThanOrEqualTo($endDate)) {
                    if ($futureBilling->greaterThanOrEqualTo($startDate)) {
                        $events[] = [
                            'date' => $futureBilling->format('Y-m-d'),
                            'module' => $subscription->module_key,
                            'amount' => $subscription->price,
                            'type' => 'billing',
                        ];
                    }
                    $futureBilling->addMonth();
                }
            }
        }
        
        // Sort by date
        usort($events, fn($a, $b) => strcmp($a['date'], $b['date']));
        
        return $events;
    }

    // ─── Private Methods ──────────────────────────────────────────────────────

    private function calculateMonthlyBilling(
        TenantModuleSubscription $subscription,
        Carbon $fromDate
    ): Carbon {
        // Check if there's a preferred billing day
        $tenantSubscription = $subscription->tenant->activeSubscription();
        
        if ($tenantSubscription && $tenantSubscription->period_ends_at) {
            // Align to tenant's billing period
            $periodEnd = Carbon::parse($tenantSubscription->period_ends_at);
            
            if ($periodEnd->greaterThan($fromDate)) {
                return $periodEnd;
            }
        }
        
        return $fromDate->copy()->addMonth();
    }

    private function calculateYearlyBilling(
        TenantModuleSubscription $subscription,
        Carbon $fromDate
    ): Carbon {
        $tenantSubscription = $subscription->tenant->activeSubscription();
        
        if ($tenantSubscription && $tenantSubscription->period_ends_at) {
            $periodEnd = Carbon::parse($tenantSubscription->period_ends_at);
            
            if ($periodEnd->greaterThan($fromDate)) {
                return $periodEnd;
            }
        }
        
        return $fromDate->copy()->addYear();
    }

    private function calculatePlanAlignedBilling(
        TenantModuleSubscription $subscription,
        Carbon $fromDate
    ): Carbon {
        $tenantSubscription = $subscription->tenant->activeSubscription();
        
        if ($tenantSubscription && $tenantSubscription->period_ends_at) {
            return Carbon::parse($tenantSubscription->period_ends_at);
        }
        
        return $fromDate->copy()->addMonth();
    }

    private function calculateTrialDays(
        TenantModuleSubscription $subscription,
        $module
    ): int {
        // Check if plan has trial override
        $planModule = $subscription->tenant->plan?->planModules()
            ->where('module_key', $subscription->module_key)
            ->first();
        
        if ($planModule && $planModule->trial_days > 0) {
            return $planModule->trial_days;
        }
        
        // Use module default
        return $module->trial_days ?? config('modules.trial.default_days', 14);
    }

    private function calculateAlignmentCharge(
        TenantModuleSubscription $subscription,
        int $days
    ): float {
        $monthlyPrice = $subscription->price;
        $dailyRate = $monthlyPrice / 30;
        
        return round($dailyRate * $days, 2);
    }
}
