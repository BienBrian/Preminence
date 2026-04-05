<?php

namespace App\Http\Controllers\Dashboard;

use App\DTOs\ModuleInstallRequest;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use App\Services\Modules\ModuleInstaller;
use App\Services\Modules\PricingCalculator;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    private ModuleRepositoryInterface $moduleRepository;
    private ModuleInstaller $installer;
    private PricingCalculator $pricingCalculator;

    public function __construct(
        ModuleRepositoryInterface $moduleRepository,
        ModuleInstaller $installer,
        PricingCalculator $pricingCalculator
    ) {
        $this->middleware(['auth', 'tenant.active']);
        $this->moduleRepository = $moduleRepository;
        $this->installer = $installer;
        $this->pricingCalculator = $pricingCalculator;
    }

    /**
     * Browse marketplace modules.
     */
    public function index(Request $request)
    {
        $tenant = tenant();
        
        $filters = $request->only(['category', 'price_type', 'search']);
        $filters['sort_by'] = $request->get('sort_by', 'sort_order');
        
        $modules = $this->moduleRepository->search($filters, 12);
        $categories = \App\Models\ModuleCategory::active()->get();
        
        // Enrich with tenant-specific info
        foreach ($modules as $module) {
            $module->can_install = $this->moduleRepository->canInstall($module->key, $tenant);
            $module->is_installed = $tenant->hasModule($module->key);
            $module->price_info = $this->pricingCalculator->calculate($module->key, $tenant);
            $module->dependencies = $this->moduleRepository->getDependencyStatus($module->key, $tenant);
            
            // Check if in trial
            if ($module->is_installed) {
                $subscription = $tenant->moduleSubscriptions()
                    ->where('module_key', $module->key)
                    ->first();
                $module->trial_ends_at = $subscription?->trial_ends_at;
                $module->is_in_trial = $subscription?->isInTrial() ?? false;
            }
        }

        return view('dashboard.marketplace.index', compact('modules', 'categories'));
    }

    /**
     * Show module details.
     */
    public function show(string $moduleKey)
    {
        $tenant = tenant();
        $module = $this->moduleRepository->findByKey($moduleKey);
        
        if (!$module || !$module->is_public) {
            abort(404);
        }

        // Get tenant-specific info
        $canInstall = $this->moduleRepository->canInstall($moduleKey, $tenant);
        $isInstalled = $tenant->hasModule($moduleKey);
        $price = $this->pricingCalculator->calculate($moduleKey, $tenant);
        $dependencies = $this->moduleRepository->getDependencyStatus($moduleKey, $tenant);
        
        // Check plan module config
        $planModule = $tenant->plan?->planModules()
            ->where('module_key', $moduleKey)
            ->first();

        // Get current subscription if installed
        $subscription = null;
        if ($isInstalled) {
            $subscription = $tenant->moduleSubscriptions()
                ->where('module_key', $moduleKey)
                ->first();
        }

        return view('dashboard.marketplace.show', compact(
            'module', 'canInstall', 'isInstalled', 'price', 
            'dependencies', 'planModule', 'subscription'
        ));
    }

    /**
     * Show installation confirmation page.
     */
    public function installForm(string $moduleKey)
    {
        $tenant = tenant();
        $module = $this->moduleRepository->findByKey($moduleKey);
        
        if (!$module) {
            abort(404);
        }

        // Check if can install
        if (!$this->moduleRepository->canInstall($moduleKey, $tenant)) {
            return redirect()
                ->route('marketplace.index')
                ->with('error', 'This module cannot be installed on your current plan.');
        }

        $dependencies = $this->moduleRepository->getDependencyStatus($moduleKey, $tenant);
        $price = $this->pricingCalculator->calculate($moduleKey, $tenant);
        
        // Calculate prorated price
        $prorated = $this->pricingCalculator->calculateProrated($moduleKey, $tenant, 'monthly');

        return view('dashboard.marketplace.install', compact(
            'module', 'dependencies', 'price', 'prorated'
        ));
    }

    /**
     * Process module installation.
     */
    public function install(Request $request, string $moduleKey)
    {
        $tenant = tenant();
        
        $validated = $request->validate([
            'billing_cycle' => 'required|in:monthly,yearly',
            'agree_terms' => 'required|accepted',
        ]);

        // Double-check permission
        if (!$this->moduleRepository->canInstall($moduleKey, $tenant)) {
            return response()->json([
                'success' => false,
                'error' => 'Module cannot be installed',
            ], 403);
        }

        $installRequest = new ModuleInstallRequest(
            moduleKey: $moduleKey,
            tenant: $tenant,
            requestedBy: $request->user(),
            billingCycle: $validated['billing_cycle'],
        );

        try {
            // Check if requires immediate payment
            $price = $this->pricingCalculator->calculate($moduleKey, $tenant, $validated['billing_cycle']);
            
            if ($price->getPrice($validated['billing_cycle']) > 0 && !$price->isFree()) {
                // For paid modules, queue installation after payment
                // This is where you'd integrate with payment gateway
                $subscription = $this->installer->queueInstall($installRequest);
                
                return response()->json([
                    'success' => true,
                    'requires_payment' => true,
                    'subscription_id' => $subscription->id,
                    'status' => $subscription->status,
                    'redirect_url' => route('marketplace.payment', $subscription),
                ]);
            }

            // Free module - install immediately
            $result = $this->installer->install($installRequest);

            if ($result->success) {
                return response()->json([
                    'success' => true,
                    'subscription_id' => $result->subscription->id,
                    'status' => $result->subscription->status,
                    'redirect_url' => route('my-modules.index'),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result->error,
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check installation status.
     */
    public function installationStatus(\App\Models\TenantModuleSubscription $subscription)
    {
        // Ensure user can only view their own tenant's subscriptions
        if ($subscription->tenant_id !== tenant()->id) {
            abort(403);
        }

        return response()->json([
            'subscription_id' => $subscription->id,
            'status' => $subscription->status,
            'progress' => $subscription->getInstallationProgress(),
            'log' => $subscription->installation_log,
            'error' => $subscription->installation_error,
        ]);
    }

    /**
     * Show payment page for module.
     */
    public function payment(\App\Models\TenantModuleSubscription $subscription)
    {
        if ($subscription->tenant_id !== tenant()->id) {
            abort(403);
        }

        $module = $this->moduleRepository->findByKey($subscription->module_key);
        $price = $this->pricingCalculator->calculate(
            $subscription->module_key, 
            tenant(), 
            $subscription->billing_type === 'addon_yearly' ? 'yearly' : 'monthly'
        );

        return view('dashboard.marketplace.payment', compact('subscription', 'module', 'price'));
    }

    /**
     * Process payment for module.
     */
    public function processPayment(Request $request, \App\Models\TenantModuleSubscription $subscription)
    {
        if ($subscription->tenant_id !== tenant()->id) {
            abort(403);
        }

        // TODO: Integrate with payment gateway
        // For now, just activate the subscription
        $subscription->update([
            'status' => 'active',
            'installed_at' => now(),
        ]);

        // Activate module
        \App\Models\TenantModule::updateOrCreate(
            [
                'tenant_id' => $subscription->tenant_id,
                'module' => $subscription->module_key,
            ],
            [
                'is_enabled' => true,
                'subscription_id' => $subscription->id,
                'enabled_at' => now(),
            ]
        );

        return redirect()
            ->route('my-modules.index')
            ->with('success', 'Payment successful! Module is now active.');
    }

    /**
     * API endpoint for searching modules.
     */
    public function searchApi(Request $request)
    {
        $tenant = tenant();
        
        $filters = [
            'search' => $request->get('q'),
            'category' => $request->get('category'),
            'tenant_id' => $tenant->id,
        ];

        $modules = $this->moduleRepository->search($filters, 20);

        return response()->json([
            'modules' => $modules->map(fn($m) => [
                'key' => $m->key,
                'name' => $m->name,
                'icon' => $m->icon,
                'category' => $m->category,
                'is_free' => $m->is_free,
                'price_monthly' => $m->price_monthly,
                'short_description' => $m->short_description,
                'is_installed' => $tenant->hasModule($m->key),
                'url' => route('marketplace.show', $m->key),
            ]),
        ]);
    }
}
