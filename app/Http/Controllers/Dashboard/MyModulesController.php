<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\TenantModuleSubscription;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use App\Services\Modules\ModuleBillingService;
use App\Services\Modules\ModuleInstaller;
use App\Services\Modules\PricingCalculator;
use Illuminate\Http\Request;

class MyModulesController extends Controller
{
    private ModuleRepositoryInterface $moduleRepository;
    private ModuleInstaller $installer;
    private PricingCalculator $pricingCalculator;
    private ModuleBillingService $billingService;

    public function __construct(
        ModuleRepositoryInterface $moduleRepository,
        ModuleInstaller $installer,
        PricingCalculator $pricingCalculator,
        ModuleBillingService $billingService
    ) {
        $this->middleware(['auth', 'tenant.active']);
        $this->moduleRepository = $moduleRepository;
        $this->installer = $installer;
        $this->pricingCalculator = $pricingCalculator;
        $this->billingService = $billingService;
    }

    /**
     * Display my installed modules.
     */
    public function index()
    {
        $tenant = tenant();
        
        // Get all module subscriptions
        $subscriptions = $tenant->moduleSubscriptions()
            ->with('module')
            ->orderBy('installed_at', 'desc')
            ->get();

        // Group by status
        $active = $subscriptions->where('status', 'active');
        $trials = $subscriptions->where('status', 'active')->filter->isInTrial();
        $suspended = $subscriptions->where('status', 'suspended');
        $pending = $subscriptions->whereIn('status', ['pending', 'installing']);

        // Calculate costs
        $addonCost = $this->pricingCalculator->calculateTotalAddonCost($tenant);

        // Enrich with module info
        foreach ($subscriptions as $sub) {
            $sub->module_info = $this->moduleRepository->findByKey($sub->module_key);
        }

        return view('dashboard.my-modules.index', compact(
            'subscriptions', 'active', 'trials', 'suspended', 'pending', 'addonCost'
        ));
    }

    /**
     * Show module settings page.
     */
    public function settings(TenantModuleSubscription $subscription)
    {
        // Ensure user can only access their own tenant's subscriptions
        if ($subscription->tenant_id !== tenant()->id) {
            abort(403);
        }

        $module = $this->moduleRepository->findByKey($subscription->module_key);

        return view('dashboard.my-modules.settings', compact('subscription', 'module'));
    }

    /**
     * Update module settings.
     */
    public function updateSettings(Request $request, TenantModuleSubscription $subscription)
    {
        if ($subscription->tenant_id !== tenant()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $subscription->update([
            'settings' => array_merge(
                $subscription->settings ?? [],
                $validated['settings']
            ),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Settings updated successfully.');
    }

    /**
     * Show upgrade/downgrade options.
     */
    public function billing(TenantModuleSubscription $subscription)
    {
        if ($subscription->tenant_id !== tenant()->id) {
            abort(403);
        }

        $module = $this->moduleRepository->findByKey($subscription->module_key);
        
        // Get current and alternative pricing
        $currentCycle = $subscription->billing_type === 'addon_yearly' ? 'yearly' : 'monthly';
        $monthlyPrice = $this->pricingCalculator->calculate($subscription->module_key, tenant(), 'monthly');
        $yearlyPrice = $this->pricingCalculator->calculate($subscription->module_key, tenant(), 'yearly');

        return view('dashboard.my-modules.billing', compact(
            'subscription', 'module', 'currentCycle', 'monthlyPrice', 'yearlyPrice'
        ));
    }

    /**
     * Change billing cycle.
     */
    public function changeBillingCycle(Request $request, TenantModuleSubscription $subscription)
    {
        if ($subscription->tenant_id !== tenant()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $newPrice = $this->pricingCalculator->calculate(
            $subscription->module_key, 
            tenant(), 
            $validated['billing_cycle']
        );

        $subscription->update([
            'billing_type' => $validated['billing_cycle'] === 'yearly' ? 'addon_yearly' : 'addon_monthly',
            'price' => $newPrice->getPrice($validated['billing_cycle']),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Billing cycle updated. Changes will take effect on next billing date.');
    }

    /**
     * Show cancellation confirmation.
     */
    public function cancelForm(TenantModuleSubscription $subscription)
    {
        if ($subscription->tenant_id !== tenant()->id) {
            abort(403);
        }

        // Check for dependents
        $dependents = $this->moduleRepository->getDependents($subscription->module_key, tenant());
        
        // Calculate refund
        $refundAmount = $this->pricingCalculator->calculateCancellationRefund($subscription);

        return view('dashboard.my-modules.cancel', compact('subscription', 'dependents', 'refundAmount'));
    }

    /**
     * Process cancellation.
     */
    public function cancel(Request $request, TenantModuleSubscription $subscription)
    {
        if ($subscription->tenant_id !== tenant()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
            'purge_data' => 'boolean',
            'confirm_uninstall' => 'required|accepted',
        ]);

        try {
            // Process refund if applicable
            $refund = $this->billingService->processRefund($subscription);

            // Uninstall
            $this->installer->uninstall(
                $subscription, 
                $validated['reason'],
                $validated['purge_data'] ?? false
            );

            $message = 'Module has been uninstalled.';
            if ($refund['refunded']) {
                $message .= ' A refund of KES ' . number_format($refund['amount'], 2) . ' will be processed.';
            }

            return redirect()
                ->route('my-modules.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle module feature flag.
     */
    public function toggleFeature(Request $request, TenantModuleSubscription $subscription)
    {
        if ($subscription->tenant_id !== tenant()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'feature' => 'required|string',
        ]);

        $features = $subscription->features_enabled ?? [];
        $features[$validated['feature']] = !($features[$validated['feature']] ?? false);

        $subscription->update(['features_enabled' => $features]);

        return response()->json([
            'success' => true,
            'feature' => $validated['feature'],
            'enabled' => $features[$validated['feature']],
        ]);
    }

    /**
     * Get module usage statistics.
     */
    public function usage(TenantModuleSubscription $subscription)
    {
        if ($subscription->tenant_id !== tenant()->id) {
            abort(403);
        }

        $module = $this->moduleRepository->findByKey($subscription->module_key);

        return view('dashboard.my-modules.usage', compact('subscription', 'module'));
    }

    /**
     * Get installation progress (AJAX).
     */
    public function progress(TenantModuleSubscription $subscription)
    {
        if ($subscription->tenant_id !== tenant()->id) {
            abort(403);
        }

        return response()->json([
            'subscription_id' => $subscription->id,
            'status' => $subscription->status,
            'progress' => $subscription->getInstallationProgress(),
            'is_complete' => $subscription->status === 'active',
            'log' => $subscription->installation_log,
        ]);
    }
}
