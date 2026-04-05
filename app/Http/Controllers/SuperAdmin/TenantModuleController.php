<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantModuleSubscription;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use App\Services\Modules\ModuleInstaller;
use App\Services\ModuleService;
use App\DTOs\ModuleInstallRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TenantModuleController extends Controller
{
    private ModuleRepositoryInterface $moduleRepository;
    private ModuleInstaller $moduleInstaller;

    public function __construct(
        ModuleRepositoryInterface $moduleRepository,
        ModuleInstaller $moduleInstaller
    ) {
        $this->middleware('auth:superadmin');
        $this->moduleRepository = $moduleRepository;
        $this->moduleInstaller = $moduleInstaller;
    }

    /**
     * Show tenant's module management page.
     */
    public function index(Tenant $tenant)
    {
        $tenant->load('plan.planModules.module', 'moduleSubscriptions');
        
        // Get all available modules for this tenant's plan
        $availableModules = $this->moduleRepository->getAvailableForTenant($tenant);
        
        // Get current module status
        $moduleStatus = $this->getModuleStatus($tenant, $availableModules);
        
        // Calculate costs
        $addonCost = $tenant->getAddonMonthlyCost();

        return view('superadmin.tenant-modules.index', compact(
            'tenant', 'availableModules', 'moduleStatus', 'addonCost'
        ));
    }

    /**
     * Grant module access to tenant.
     */
    public function grant(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'module_key' => 'required|exists:modules,key',
            'billing_type' => 'required|in:plan_included,addon_monthly,addon_yearly,complimentary,trial',
            'price' => 'nullable|numeric|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'reason' => 'nullable|string|max:1000',
        ]);

        // Check if already installed
        if ($tenant->hasModule($validated['module_key'])) {
            return redirect()
                ->back()
                ->with('warning', 'Module is already installed for this tenant.');
        }

        // Get the requesting user (superadmin)
        $adminUser = auth('superadmin')->user();
        
        // Create a system user for the tenant if needed
        $systemUser = $tenant->users()->first();
        if (!$systemUser) {
            return redirect()
                ->back()
                ->with('error', 'Tenant has no users to assign as installer.');
        }

        // Create install request
        $installRequest = new ModuleInstallRequest(
            moduleKey: $validated['module_key'],
            tenant: $tenant,
            requestedBy: $systemUser,
            billingCycle: str_contains($validated['billing_type'], 'yearly') ? 'yearly' : 'monthly',
            autoApprove: true,
            trialDays: $validated['trial_days'] ?? 0
        );

        // Override billing type if complimentary
        if ($validated['billing_type'] === 'complimentary') {
            $subscription = TenantModuleSubscription::create([
                'tenant_id' => $tenant->id,
                'module_key' => $validated['module_key'],
                'status' => 'active',
                'billing_type' => 'complimentary',
                'price' => 0,
                'installed_at' => now(),
                'next_billing_at' => null,
                'installed_by' => $systemUser->id,
                'created_by' => $adminUser->id,
                'settings' => ['granted_reason' => $validated['reason'] ?? 'Admin granted'],
            ]);

            // Activate module
            \App\Models\TenantModule::create([
                'tenant_id' => $tenant->id,
                'module' => $validated['module_key'],
                'subscription_id' => $subscription->id,
                'installed_via' => 'admin',
                'is_enabled' => true,
                'enabled_at' => now(),
                'override_by_admin' => true,
                'overridden_by' => $adminUser->id,
            ]);
        } else {
            // Standard installation
            $result = $this->moduleInstaller->install($installRequest);
            
            if (!$result->success) {
                return redirect()
                    ->back()
                    ->with('error', 'Installation failed: ' . $result->error);
            }

            $subscription = $result->subscription;
            
            // Override price if specified
            if (!empty($validated['price'])) {
                $subscription->update(['price' => $validated['price']]);
            }

            // Mark as admin override
            $subscription->update(['created_by' => $adminUser->id]);
            
            $tenantModule = \App\Models\TenantModule::where('tenant_id', $tenant->id)
                ->where('module', $validated['module_key'])
                ->first();
                
            if ($tenantModule) {
                $tenantModule->update([
                    'override_by_admin' => true,
                    'overridden_by' => $adminUser->id,
                ]);
            }
        }

        // Invalidate cache (both repository and ModuleService caches)
        $this->moduleRepository->invalidateTenant($tenant->id);
        app(ModuleService::class)->flushCache($tenant->id, $validated['module_key']);

        // Log action
        \App\Models\PlatformAuditLog::record(
            'tenant.module.granted',
            [
                'tenant_id' => $tenant->id,
                'module_key' => $validated['module_key'],
                'billing_type' => $validated['billing_type'],
                'price' => $validated['price'] ?? null,
                'reason' => $validated['reason'] ?? null,
            ],
            $tenant->id,
            $adminUser->id
        );

        return redirect()
            ->back()
            ->with('success', 'Module access granted successfully.');
    }

    /**
     * Revoke module access from tenant.
     */
    public function revoke(Request $request, Tenant $tenant, string $moduleKey)
    {
        // Verify password confirmation for dangerous action
        if (!$this->verifyPasswordConfirmation($request)) {
            return redirect()
                ->back()
                ->with('error', 'Password verification failed. Module was not revoked.');
        }

        $subscription = $tenant->moduleSubscriptions()
            ->where('module_key', $moduleKey)
            ->first();

        if (!$subscription) {
            return redirect()
                ->back()
                ->with('error', 'Module not found for this tenant.');
        }

        // Uninstall
        $this->moduleInstaller->uninstall(
            $subscription,
            'Revoked by admin via password-verified action',
            false
        );

        // Invalidate cache (both repository and ModuleService caches)
        $this->moduleRepository->invalidateTenant($tenant->id);
        app(ModuleService::class)->flushCache($tenant->id, $moduleKey);

        // Log action
        \App\Models\PlatformAuditLog::record(
            'tenant.module.revoked',
            [
                'tenant_id' => $tenant->id,
                'module_key' => $moduleKey,
                'reason' => 'Revoked by admin with password verification',
            ],
            $tenant->id,
            auth('superadmin')->id()
        );

        return redirect()
            ->back()
            ->with('success', 'Module access revoked successfully.');
    }

    /**
     * Verify password confirmation for dangerous actions.
     */
    private function verifyPasswordConfirmation(Request $request): bool
    {
        $password = $request->input('confirm_password');
        
        if (empty($password)) {
            return false;
        }

        $admin = auth('superadmin')->user();
        
        if (!$admin) {
            return false;
        }

        return Hash::check($password, $admin->password);
    }

    /**
     * Toggle module suspension.
     */
    public function toggleSuspension(Request $request, Tenant $tenant, string $moduleKey)
    {
        // Verify password confirmation for dangerous action
        if (!$this->verifyPasswordConfirmation($request)) {
            return redirect()
                ->back()
                ->with('error', 'Password verification failed. Action was not performed.');
        }

        $subscription = $tenant->moduleSubscriptions()
            ->where('module_key', $moduleKey)
            ->first();

        if (!$subscription) {
            return redirect()
                ->back()
                ->with('error', 'Module not found for this tenant.');
        }

        $isSuspending = !$subscription->isSuspended();

        if ($subscription->isSuspended()) {
            // Reactivate
            $subscription->update([
                'status' => 'active',
                'suspended_at' => null,
                'suspension_reason' => null,
            ]);

            // Re-enable module
            \App\Models\TenantModule::where('tenant_id', $tenant->id)
                ->where('module', $moduleKey)
                ->update(['is_enabled' => true]);

            $message = 'Module reactivated successfully.';
        } else {
            // Suspend
            $subscription->update([
                'status' => 'suspended',
                'suspended_at' => now(),
                'suspension_reason' => 'Admin action (password verified)',
            ]);

            // Disable module
            \App\Models\TenantModule::where('tenant_id', $tenant->id)
                ->where('module', $moduleKey)
                ->update(['is_enabled' => false]);

            $message = 'Module suspended successfully.';
        }

        // Invalidate cache (both repository and ModuleService caches)
        $this->moduleRepository->invalidateTenant($tenant->id);
        app(ModuleService::class)->flushCache($tenant->id, $moduleKey);

        return redirect()
            ->back()
            ->with('success', $message);
    }

    /**
     * Update module pricing for tenant.
     */
    public function updatePricing(Request $request, Tenant $tenant, string $moduleKey)
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'reason' => 'nullable|string|max:1000',
        ]);

        $subscription = $tenant->moduleSubscriptions()
            ->where('module_key', $moduleKey)
            ->first();

        if (!$subscription) {
            return redirect()
                ->back()
                ->with('error', 'Module subscription not found.');
        }

        $oldPrice = $subscription->price;

        $subscription->update([
            'price' => $validated['price'],
            'billing_type' => $validated['billing_cycle'] === 'yearly' ? 'addon_yearly' : 'addon_monthly',
            'settings' => array_merge(
                $subscription->settings ?? [],
                ['price_update_reason' => $validated['reason'] ?? 'Admin update']
            ),
        ]);

        // Log action
        \App\Models\PlatformAuditLog::record(
            'tenant.module.price_updated',
            [
                'tenant_id' => $tenant->id,
                'module_key' => $moduleKey,
                'old_price' => $oldPrice,
                'new_price' => $validated['price'],
                'reason' => $validated['reason'] ?? null,
            ],
            $tenant->id,
            auth('superadmin')->id()
        );

        return redirect()
            ->back()
            ->with('success', 'Module pricing updated successfully.');
    }

    /**
     * Get module status for tenant.
     */
    private function getModuleStatus(Tenant $tenant, $availableModules): array
    {
        $status = [];

        foreach ($availableModules as $module) {
            $subscription = $tenant->moduleSubscriptions()
                ->where('module_key', $module->key)
                ->first();

            $status[$module->key] = [
                'installed' => $tenant->hasModule($module->key),
                'subscription' => $subscription,
                'status' => $subscription?->status ?? 'not_installed',
                'billing_type' => $subscription?->billing_type,
                'price' => $subscription?->price,
                'is_included' => $tenant->plan?->isModuleIncluded($module->key) ?? false,
            ];
        }

        return $status;
    }
}
