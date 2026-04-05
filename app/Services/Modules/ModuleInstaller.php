<?php

namespace App\Services\Modules;

use App\DTOs\InstallationResult;
use App\DTOs\ModuleInstallRequest;
use App\Events\Modules\ModuleInstallationFailed;
use App\Events\Modules\ModuleInstalled;
use App\Events\Modules\ModuleInstallationStarted;
use App\Events\Modules\ModuleUninstalled;
use App\Exceptions\Modules\ModuleInstallationException;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\TenantModuleSubscription;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Service for installing and uninstalling modules.
 * 
 * Handles the complete module lifecycle including dependency resolution,
 * billing setup, database migrations, and cleanup.
 */
class ModuleInstaller
{
    private ModuleRepositoryInterface $moduleRepository;
    private PricingCalculator $pricingCalculator;
    private DependencyResolver $dependencyResolver;

    public function __construct(
        ModuleRepositoryInterface $moduleRepository,
        PricingCalculator $pricingCalculator,
        DependencyResolver $dependencyResolver
    ) {
        $this->moduleRepository = $moduleRepository;
        $this->pricingCalculator = $pricingCalculator;
        $this->dependencyResolver = $dependencyResolver;
    }

    /**
     * Install a module for a tenant.
     *
     * @param ModuleInstallRequest $request The installation request
     * @return InstallationResult
     * @throws ModuleInstallationException
     */
    public function install(ModuleInstallRequest $request): InstallationResult
    {
        $module = $this->moduleRepository->findByKey($request->moduleKey);
        
        if (!$module) {
            throw ModuleInstallationException::missingDependencies([$request->moduleKey]);
        }

        // Check idempotency
        if ($this->isDuplicateRequest($request)) {
            Log::info('Duplicate module installation request detected', [
                'tenant_id' => $request->tenant->id,
                'module' => $request->moduleKey,
                'idempotency_key' => $request->getIdempotencyKey(),
            ]);
            
            $existing = $this->findExistingSubscription($request);
            return InstallationResult::success($existing);
        }

        // Validate installation
        if (!$this->moduleRepository->canInstall($request->moduleKey, $request->tenant)) {
            throw ModuleInstallationException::planRestricted(
                $request->moduleKey,
                'Module cannot be installed for this tenant'
            );
        }

        return DB::transaction(function () use ($request, $module) {
            // Create subscription record
            $subscription = $this->createSubscription($request);
            
            // Dispatch started event
            event(new ModuleInstallationStarted($module, $request->tenant, $subscription));
            
            try {
                // Execute installation steps
                $steps = $this->executeInstallation($subscription, $request);
                
                // Activate module
                $this->activateModule($subscription, $request->tenant);
                
                // Update subscription status
                $subscription->update([
                    'status' => 'active',
                    'installed_at' => now(),
                    'version_installed' => $module->version,
                ]);
                
                // Dispatch success event
                event(new ModuleInstalled($module, $request->tenant, $subscription, $request->requestedBy));
                
                return InstallationResult::success(
                    subscription: $subscription,
                    steps: $steps,
                    redirectUrl: route('my-modules.index')
                );
                
            } catch (Throwable $e) {
                return $this->handleInstallationFailure($subscription, $e, $request);
            }
        });
    }

    /**
     * Install with automatic dependency resolution.
     * 
     * Installs all required dependencies before the main module.
     *
     * @param ModuleInstallRequest $request The installation request
     * @return array Results for each installed module
     */
    public function installWithDependencies(ModuleInstallRequest $request): array
    {
        $dependencies = $this->dependencyResolver->resolve($request->moduleKey, $request->tenant);
        $results = [];
        
        // Install dependencies first
        foreach ($dependencies as $dep) {
            if (!$dep['installed'] && $dep['required']) {
                $depRequest = $request->with(
                    moduleKey: $dep['key'],
                    autoApprove: true // Dependencies auto-approved
                );
                
                $results[$dep['key']] = $this->install($depRequest);
            }
        }
        
        // Install main module
        $results[$request->moduleKey] = $this->install($request);
        
        return $results;
    }

    /**
     * Queue installation for background processing.
     * 
     * Use this for long-running installations.
     *
     * @param ModuleInstallRequest $request The installation request
     * @return TenantModuleSubscription The queued subscription
     */
    public function queueInstall(ModuleInstallRequest $request): TenantModuleSubscription
    {
        $subscription = $this->createSubscription($request);
        
        // Dispatch to queue
        \App\Jobs\ModuleInstallationJob::dispatch($subscription, $request)
            ->onQueue(config('modules.installation.queue', 'default'));
        
        return $subscription;
    }

    /**
     * Uninstall a module.
     * 
     * @param TenantModuleSubscription $subscription The subscription to cancel
     * @param string|null $reason Reason for uninstallation
     * @param bool $purgeData Whether to remove module data
     * @return bool Success status
     * @throws ModuleInstallationException If dependents exist
     */
    public function uninstall(
        TenantModuleSubscription $subscription, 
        ?string $reason = null, 
        bool $purgeData = false
    ): bool {
        $module = $this->moduleRepository->findByKey($subscription->module_key);
        
        return DB::transaction(function () use ($subscription, $module, $reason, $purgeData) {
            // Check for dependents
            $dependents = $this->dependencyResolver->getDependents(
                $subscription->module_key, 
                $subscription->tenant
            );
            
            if (!empty($dependents)) {
                throw ModuleInstallationException::moduleConflict(
                    $subscription->module_key,
                    array_column($dependents, 'name')
                );
            }
            
            $subscription->update([
                'status' => 'uninstalling',
                'cancellation_reason' => $reason,
                'cancelled_by' => auth()->id(),
            ]);
            
            try {
                if ($purgeData) {
                    $this->purgeModuleData($subscription);
                }
                
                // Disable in tenant_modules
                TenantModule::where('tenant_id', $subscription->tenant_id)
                    ->where('module', $subscription->module_key)
                    ->update([
                        'is_enabled' => false,
                        'disabled_at' => now(),
                    ]);
                
                $subscription->update([
                    'status' => 'uninstalled',
                    'unsubscribed_at' => now(),
                ]);
                
                // Clear cache
                app(\App\Services\ModuleService::class)->flushCache(
                    $subscription->tenant_id, 
                    $subscription->module_key
                );
                
                event(new ModuleUninstalled(
                    $module, 
                    $subscription->tenant, 
                    $subscription,
                    $reason
                ));
                
                return true;
                
            } catch (Throwable $e) {
                $subscription->update([
                    'status' => 'failed',
                    'installation_error' => $e->getMessage(),
                ]);
                
                throw $e;
            }
        });
    }

    /**
     * Retry a failed installation.
     * 
     * @param TenantModuleSubscription $subscription The failed subscription
     * @return InstallationResult
     */
    public function retry(TenantModuleSubscription $subscription): InstallationResult
    {
        if ($subscription->status !== 'failed') {
            throw new \InvalidArgumentException('Only failed subscriptions can be retried');
        }

        // Reset status and retry
        $subscription->update([
            'status' => 'pending',
            'installation_error' => null,
            'installation_log' => [],
        ]);

        $request = new ModuleInstallRequest(
            moduleKey: $subscription->module_key,
            tenant: $subscription->tenant,
            requestedBy: auth()->user() ?? $subscription->installer,
            billingCycle: str_replace('addon_', '', $subscription->billing_type),
        );

        return $this->install($request);
    }

    // ─── Private Methods ──────────────────────────────────────────────────────

    /**
     * Create subscription record.
     */
    private function createSubscription(ModuleInstallRequest $request): TenantModuleSubscription
    {
        $price = $this->pricingCalculator->calculate(
            $request->moduleKey, 
            $request->tenant, 
            $request->billingCycle
        );
        
        $module = $this->moduleRepository->findByKey($request->moduleKey);
        
        // Determine billing type
        $billingType = $this->determineBillingType($request, $module);
        
        // Calculate trial end date
        $trialDays = $this->getTrialDays($request, $module);
        $trialEndsAt = $trialDays > 0 ? now()->addDays($trialDays) : null;

        return TenantModuleSubscription::create([
            'tenant_id' => $request->tenant->id,
            'module_key' => $request->moduleKey,
            'status' => 'pending',
            'billing_type' => $billingType,
            'price' => $price->getPrice($request->billingCycle),
            'currency' => $price->currency,
            'idempotency_key' => $request->getIdempotencyKey(),
            'trial_ends_at' => $trialEndsAt,
            'next_billing_at' => $trialEndsAt ? null : $this->calculateNextBilling($request->billingCycle),
            'settings' => $request->settings,
            'installed_by' => $request->requestedBy->id,
            'created_by' => auth('superadmin')->id(),
        ]);
    }

    /**
     * Execute installation steps.
     */
    private function executeInstallation(
        TenantModuleSubscription $subscription, 
        ModuleInstallRequest $request
    ): array {
        $module = $this->moduleRepository->findByKey($subscription->module_key);
        $steps = [];
        
        // Step 1: Pre-install hooks
        $subscription->logInstallationStep('pre_install', 'running');
        $this->runPreInstallHooks($module, $request->tenant);
        $subscription->logInstallationStep('pre_install', 'complete');
        $steps[] = ['step' => 'pre_install', 'status' => 'complete'];
        
        // Step 2: Run migrations
        if ($module->migration_path) {
            $subscription->logInstallationStep('migrations', 'running');
            $this->runMigrations($module, $request->tenant);
            $subscription->logInstallationStep('migrations', 'complete');
            $steps[] = ['step' => 'migrations', 'status' => 'complete'];
        }
        
        // Step 3: Run seeders
        if ($module->seeder_class) {
            $subscription->logInstallationStep('seeders', 'running');
            $this->runSeeders($module, $request->tenant);
            $subscription->logInstallationStep('seeders', 'complete');
            $steps[] = ['step' => 'seeders', 'status' => 'complete'];
        }
        
        // Step 4: Post-install hooks
        $subscription->logInstallationStep('post_install', 'running');
        $this->runPostInstallHooks($module, $request->tenant);
        $subscription->logInstallationStep('post_install', 'complete');
        $steps[] = ['step' => 'post_install', 'status' => 'complete'];
        
        return $steps;
    }

    /**
     * Activate module for tenant.
     */
    private function activateModule(TenantModuleSubscription $subscription, Tenant $tenant): void
    {
        TenantModule::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'module' => $subscription->module_key,
            ],
            [
                'is_enabled' => true,
                'subscription_id' => $subscription->id,
                'installed_via' => $subscription->billing_type === 'plan_included' ? 'plan' : 'marketplace',
                'enabled_at' => now(),
                'disabled_at' => null,
            ]
        );
        
        // Clear cache
        app(\App\Services\ModuleService::class)->flushCache($tenant->id, $subscription->module_key);
        
        // Invalidate repository cache
        $this->moduleRepository->invalidateTenant($tenant->id);
    }

    /**
     * Handle installation failure.
     */
    private function handleInstallationFailure(
        TenantModuleSubscription $subscription, 
        Throwable $e,
        ModuleInstallRequest $request
    ): InstallationResult {
        Log::error('Module installation failed', [
            'subscription_id' => $subscription->id,
            'tenant_id' => $request->tenant->id,
            'module' => $request->moduleKey,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        $subscription->update([
            'status' => 'failed',
            'installation_error' => $e->getMessage(),
        ]);
        
        $module = $this->moduleRepository->findByKey($request->moduleKey);
        event(new ModuleInstallationFailed($module, $request->tenant, $subscription, $e));
        
        return InstallationResult::failure($subscription, $e->getMessage());
    }

    /**
     * Determine billing type based on request and module.
     */
    private function determineBillingType(ModuleInstallRequest $request, \App\Models\Module $module): string
    {
        // Check if included in plan
        $planModule = $request->tenant->plan?->planModules()
            ->where('module_key', $module->key)
            ->first();
        
        if ($planModule?->is_included) {
            return 'plan_included';
        }
        
        // Check for trial
        $trialDays = $this->getTrialDays($request, $module);
        if ($trialDays > 0) {
            return 'trial';
        }
        
        // Standard addon billing
        return $request->billingCycle === 'yearly' ? 'addon_yearly' : 'addon_monthly';
    }

    /**
     * Get trial days for module.
     */
    private function getTrialDays(ModuleInstallRequest $request, \App\Models\Module $module): int
    {
        if ($request->trialDays !== null) {
            return $request->trialDays;
        }
        
        $planModule = $request->tenant->plan?->planModules()
            ->where('module_key', $module->key)
            ->first();
        
        return $planModule?->getTrialDays() ?? 0;
    }

    /**
     * Check for duplicate request.
     */
    private function isDuplicateRequest(ModuleInstallRequest $request): bool
    {
        if (!$request->idempotencyKey) {
            return false;
        }
        
        return TenantModuleSubscription::where('idempotency_key', $request->idempotencyKey)
            ->where('tenant_id', $request->tenant->id)
            ->where('created_at', '>=', now()->subHour())
            ->exists();
    }

    /**
     * Find existing subscription.
     */
    private function findExistingSubscription(ModuleInstallRequest $request): TenantModuleSubscription
    {
        return TenantModuleSubscription::where('idempotency_key', $request->getIdempotencyKey())
            ->where('tenant_id', $request->tenant->id)
            ->first();
    }

    /**
     * Calculate next billing date.
     */
    private function calculateNextBilling(string $billingCycle): \Carbon\Carbon
    {
        return $billingCycle === 'yearly' 
            ? now()->addYear() 
            : now()->addMonth();
    }

    // ─── Hook Methods (Extensible) ────────────────────────────────────────────

    /**
     * Run pre-install hooks.
     */
    protected function runPreInstallHooks(\App\Models\Module $module, Tenant $tenant): void
    {
        // Override in subclass or use event listeners
    }

    /**
     * Run module migrations.
     */
    protected function runMigrations(\App\Models\Module $module, Tenant $tenant): void
    {
        if (!$module->migration_path) {
            return;
        }

        // Module-specific migrations would run here
        // This is a placeholder for the actual implementation
    }

    /**
     * Run module seeders.
     */
    protected function runSeeders(\App\Models\Module $module, Tenant $tenant): void
    {
        if (!$module->seeder_class) {
            return;
        }

        // Module-specific seeders would run here
    }

    /**
     * Run post-install hooks.
     */
    protected function runPostInstallHooks(\App\Models\Module $module, Tenant $tenant): void
    {
        // Override in subclass or use event listeners
    }

    /**
     * Purge module data on uninstall.
     */
    protected function purgeModuleData(TenantModuleSubscription $subscription): void
    {
        // Data cleanup would happen here
        // Be very careful with this - usually better to archive than delete
    }
}
