<?php

namespace App\Services\Modules;

use App\Models\ModuleInvoiceItem;
use App\Models\Tenant;
use App\Models\TenantModuleSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for generating and managing module invoice items.
 * 
 * Handles invoice item creation, proration calculations, and billing summaries.
 */
class ModuleInvoiceService
{
    private ProrationCalculator $prorationCalculator;

    public function __construct(ProrationCalculator $prorationCalculator)
    {
        $this->prorationCalculator = $prorationCalculator;
    }

    /**
     * Generate invoice items for all due module subscriptions.
     * 
     * @param Carbon|null $asOfDate Date to check for billing (default: now)
     * @return array Statistics on generated items
     */
    public function generateDueInvoiceItems(?Carbon $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? now();
        
        $dueSubscriptions = TenantModuleSubscription::pendingBilling()
            ->where('next_billing_at', '<=', $asOfDate)
            ->get();

        $stats = [
            'processed' => 0,
            'created' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        foreach ($dueSubscriptions as $subscription) {
            $stats['processed']++;
            
            try {
                $existingItem = ModuleInvoiceItem::where('subscription_id', $subscription->id)
                    ->where('status', 'pending')
                    ->where('type', $this->getRecurringType($subscription))
                    ->where('period_start', $subscription->next_billing_at->format('Y-m-d'))
                    ->exists();
                
                if ($existingItem) {
                    $stats['skipped']++;
                    continue;
                }

                $this->createRecurringInvoiceItem($subscription);
                $stats['created']++;
                
            } catch (\Exception $e) {
                $stats['errors'][] = [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ];
                Log::error('Failed to generate invoice item', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $stats;
    }

    /**
     * Create invoice item for initial module installation.
     * 
     * @param TenantModuleSubscription $subscription
     * @param string $billingCycle 'monthly' or 'yearly'
     * @param bool $isProrated Whether to prorate for mid-cycle installation
     * @return ModuleInvoiceItem
     */
    public function createInitialInvoiceItem(
        TenantModuleSubscription $subscription,
        string $billingCycle = 'monthly',
        bool $isProrated = true
    ): ModuleInvoiceItem {
        return DB::transaction(function () use ($subscription, $billingCycle, $isProrated) {
            $module = $subscription->module;
            $tenant = $subscription->tenant;
            
            // Calculate amounts
            if ($isProrated) {
                $proration = $this->prorationCalculator->calculateInstallProration(
                    $subscription,
                    $billingCycle
                );
                
                $items = [];
                
                // Setup fee (not prorated)
                if ($proration['setup_fee'] > 0) {
                    $items[] = $this->createInvoiceItem($subscription, [
                        'type' => 'setup_fee',
                        'description' => "{$module->name} - Setup Fee",
                        'unit_price' => $proration['setup_fee'],
                        'amount' => $proration['setup_fee'],
                        'total_amount' => $proration['setup_fee'],
                    ]);
                }
                
                // Prorated subscription charge
                if ($proration['prorated_amount'] > 0) {
                    $items[] = $this->createInvoiceItem($subscription, [
                        'type' => 'prorated_charge',
                        'description' => "{$module->name} - Prorated {$billingCycle} subscription ({$proration['days_remaining']} days)",
                        'unit_price' => $proration['prorated_amount'],
                        'amount' => $proration['prorated_amount'],
                        'total_amount' => $proration['prorated_amount'],
                        'period_start' => $proration['period_start'],
                        'period_end' => $proration['period_end'],
                        'days_billed' => $proration['days_remaining'],
                        'proration_details' => $proration,
                    ]);
                }
                
                // Full period item (pending for next cycle)
                $this->createInvoiceItem($subscription, [
                    'type' => $billingCycle === 'yearly' ? 'yearly_recurring' : 'monthly_recurring',
                    'description' => "{$module->name} - {$billingCycle} subscription",
                    'unit_price' => $subscription->price,
                    'amount' => $subscription->price,
                    'total_amount' => $subscription->price,
                    'period_start' => $proration['period_end']->copy()->addDay(),
                    'period_end' => $billingCycle === 'yearly' 
                        ? $proration['period_end']->copy()->addYear() 
                        : $proration['period_end']->copy()->addMonth(),
                    'status' => 'pending', // Don't bill yet
                ]);
                
                return $items[0] ?? null;
                
            } else {
                // Full period charge
                return $this->createInvoiceItem($subscription, [
                    'type' => $billingCycle === 'yearly' ? 'yearly_recurring' : 'monthly_recurring',
                    'description' => "{$module->name} - {$billingCycle} subscription",
                    'unit_price' => $subscription->price,
                    'amount' => $subscription->price,
                    'total_amount' => $subscription->price,
                    'period_start' => now(),
                    'period_end' => $billingCycle === 'yearly' ? now()->addYear() : now()->addMonth(),
                ]);
            }
        });
    }

    /**
     * Create refund invoice item for cancellation.
     * 
     * @param TenantModuleSubscription $subscription
     * @param float $refundAmount
     * @return ModuleInvoiceItem|null
     */
    public function createRefundInvoiceItem(
        TenantModuleSubscription $subscription,
        float $refundAmount
    ): ?ModuleInvoiceItem {
        if ($refundAmount <= 0) {
            return null;
        }

        return $this->createInvoiceItem($subscription, [
            'type' => 'refund',
            'description' => "{$subscription->module->name} - Prorated refund for cancellation",
            'unit_price' => -$refundAmount,
            'amount' => -$refundAmount,
            'total_amount' => -$refundAmount,
            'notes' => 'Automatic prorated refund upon cancellation',
        ]);
    }

    /**
     * Create invoice item for billing cycle change.
     * 
     * @param TenantModuleSubscription $subscription
     * @param string $newCycle
     * @param float $difference
     * @return ModuleInvoiceItem|null
     */
    public function createBillingCycleChangeItem(
        TenantModuleSubscription $subscription,
        string $newCycle,
        float $difference
    ): ?ModuleInvoiceItem {
        if (abs($difference) < 0.01) {
            return null;
        }

        $type = $difference > 0 ? 'prorated_charge' : 'prorated_credit';
        
        return $this->createInvoiceItem($subscription, [
            'type' => $type,
            'description' => "{$subscription->module->name} - Billing cycle change adjustment (to {$newCycle})",
            'unit_price' => $difference,
            'amount' => $difference,
            'total_amount' => $difference,
            'notes' => "Adjustment for changing to {$newCycle} billing",
        ]);
    }

    /**
     * Get pending invoice items for a tenant.
     * 
     * @param Tenant $tenant
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingItemsForTenant(Tenant $tenant)
    {
        return ModuleInvoiceItem::with('subscription.module')
            ->where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get invoice summary for a tenant.
     * 
     * @param Tenant $tenant
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array
     */
    public function getInvoiceSummary(Tenant $tenant, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = ModuleInvoiceItem::where('tenant_id', $tenant->id);
        
        if ($startDate) {
            $query->where('period_start', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('period_end', '<=', $endDate);
        }

        $items = $query->get();

        return [
            'total_pending' => $items->where('status', 'pending')->sum('total_amount'),
            'total_invoiced' => $items->where('status', 'invoiced')->sum('total_amount'),
            'total_paid' => $items->where('status', 'paid')->sum('total_amount'),
            'total_refunded' => $items->where('status', 'refunded')->sum('total_amount'),
            'item_count' => $items->count(),
            'by_type' => $items->groupBy('type')->map->sum('total_amount'),
            'by_module' => $items->groupBy('module_key')->map->sum('total_amount'),
        ];
    }

    /**
     * Mark pending items as invoiced and generate invoice number.
     * 
     * @param Tenant $tenant
     * @return array Invoice details
     */
    public function finalizeInvoice(Tenant $tenant): array
    {
        $pendingItems = $this->getPendingItemsForTenant($tenant);
        
        if ($pendingItems->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No pending items to invoice',
            ];
        }

        $invoiceNumber = $this->generateInvoiceNumber($tenant);
        $total = $pendingItems->sum('total_amount');

        foreach ($pendingItems as $item) {
            $item->markAsInvoiced($invoiceNumber);
        }

        return [
            'success' => true,
            'invoice_number' => $invoiceNumber,
            'total_amount' => $total,
            'currency' => 'KES',
            'items' => $pendingItems,
            'item_count' => $pendingItems->count(),
        ];
    }

    // ─── Private Methods ──────────────────────────────────────────────────────

    private function createRecurringInvoiceItem(TenantModuleSubscription $subscription): ModuleInvoiceItem
    {
        $module = $subscription->module;
        $billingType = $subscription->billing_type;
        
        $periodStart = $subscription->next_billing_at;
        $periodEnd = $billingType === 'addon_yearly' 
            ? $periodStart->copy()->addYear() 
            : $periodStart->copy()->addMonth();

        return $this->createInvoiceItem($subscription, [
            'type' => $billingType === 'addon_yearly' ? 'yearly_recurring' : 'monthly_recurring',
            'description' => "{$module->name} - " . ($billingType === 'addon_yearly' ? 'Yearly' : 'Monthly') . ' subscription',
            'unit_price' => $subscription->price,
            'amount' => $subscription->price,
            'total_amount' => $subscription->price,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
        ]);
    }

    private function createInvoiceItem(
        TenantModuleSubscription $subscription,
        array $data
    ): ModuleInvoiceItem {
        return ModuleInvoiceItem::create(array_merge([
            'subscription_id' => $subscription->id,
            'tenant_id' => $subscription->tenant_id,
            'module_key' => $subscription->module_key,
            'currency' => $subscription->currency ?? 'KES',
            'status' => 'pending',
            'tax_amount' => 0,
        ], $data));
    }

    private function getRecurringType(TenantModuleSubscription $subscription): string
    {
        return $subscription->billing_type === 'addon_yearly' 
            ? 'yearly_recurring' 
            : 'monthly_recurring';
    }

    private function generateInvoiceNumber(Tenant $tenant): string
    {
        $prefix = 'INV';
        $tenantCode = str_pad($tenant->id, 4, '0', STR_PAD_LEFT);
        $date = now()->format('Ymd');
        $sequence = ModuleInvoiceItem::where('invoice_number', 'like', "{$prefix}-{$tenantCode}-{$date}%")
            ->distinct()
            ->count('invoice_number') + 1;
        
        return sprintf('%s-%s-%s-%04d', $prefix, $tenantCode, $date, $sequence);
    }
}
