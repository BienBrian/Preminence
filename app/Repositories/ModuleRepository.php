<?php

namespace App\Repositories;

use App\Models\Module;
use App\Models\Tenant;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ModuleRepository implements ModuleRepositoryInterface
{
    /**
     * Cache TTL in seconds.
     */
    protected int $cacheTtl = 300; // 5 minutes

    public function findByKey(string $key): ?Module
    {
        return Module::where('key', $key)->first();
    }

    public function getMarketplaceModules(): Collection
    {
        return Module::forMarketplace()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function getAvailableForTenant(Tenant $tenant): Collection
    {
        $plan = $tenant->plan;
        
        if (!$plan) {
            // Free tier - only core and free modules
            return Module::forMarketplace()
                ->where(function ($q) {
                    $q->whereIn('category', ['core', 'people', 'engagement'])
                      ->orWhere('is_free', true);
                })
                ->get();
        }
        
        // Get plan modules that are included or available
        $planModuleKeys = $plan->planModules()
            ->where(function ($q) {
                $q->where('is_included', true)
                  ->orWhere('is_available', true);
            })
            ->pluck('module_key');
        
        return Module::forMarketplace()
            ->whereIn('key', $planModuleKeys)
            ->orderBy('sort_order')
            ->get();
    }

    public function getForPlan(int $planId): Collection
    {
        return Module::whereHas('planModules', function ($q) use ($planId) {
            $q->where('plan_id', $planId);
        })
        ->with(['planModules' => function ($q) use ($planId) {
            $q->where('plan_id', $planId);
        }])
        ->orderBy('category')
        ->orderBy('sort_order')
        ->get();
    }

    public function search(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Module::forMarketplace();
        
        // Category filter
        if (!empty($filters['category'])) {
            $query->inCategory($filters['category']);
        }
        
        // Price type filter
        if (!empty($filters['price_type'])) {
            match($filters['price_type']) {
                'free' => $query->free(),
                'paid' => $query->paid(),
                default => null,
            };
        }
        
        // Search term filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereJsonContains('tags', $search);
            });
        }
        
        // Trial availability filter
        if (!empty($filters['has_trial'])) {
            $query->where('trial_days', '>', 0);
        }

        // Installed status filter (requires tenant context)
        if (!empty($filters['installed']) && !empty($filters['tenant_id'])) {
            $installedKeys = DB::table('tenant_modules')
                ->where('tenant_id', $filters['tenant_id'])
                ->where('is_enabled', true)
                ->pluck('module');
            
            if ($filters['installed'] === 'yes') {
                $query->whereIn('key', $installedKeys);
            } elseif ($filters['installed'] === 'no') {
                $query->whereNotIn('key', $installedKeys);
            }
        }
        
        // Sorting
        $sortBy = $filters['sort_by'] ?? 'sort_order';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        
        $allowedSorts = ['name', 'price_monthly', 'sort_order', 'created_at', 'category'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }
        
        return $query->paginate($perPage);
    }

    public function canInstall(string $moduleKey, Tenant $tenant): bool
    {
        // Check if already installed
        $existing = DB::table('tenant_module_subscriptions')
            ->where('tenant_id', $tenant->id)
            ->where('module_key', $moduleKey)
            ->whereIn('status', ['active', 'pending', 'installing'])
            ->exists();
        
        if ($existing) {
            return false;
        }
        
        $module = $this->findByKey($moduleKey);
        if (!$module || !$module->is_active) {
            return false;
        }
        
        // Check plan restrictions
        if (!$this->planAllowsInstallation($moduleKey, $tenant)) {
            return false;
        }
        
        // Check dependencies
        $deps = $this->getDependencyStatus($moduleKey, $tenant);
        foreach ($deps as $dep) {
            if ($dep['required'] && !$dep['installed']) {
                return false;
            }
        }
        
        // Check conflicts
        foreach ($module->conflicts ?? [] as $conflict) {
            if ($tenant->hasModule($conflict)) {
                return false;
            }
        }
        
        return true;
    }

    public function getDependencyStatus(string $moduleKey, Tenant $tenant): array
    {
        $module = $this->findByKey($moduleKey);
        if (!$module) {
            return [];
        }
        
        $dependencies = [];
        
        foreach ($module->dependencies ?? [] as $depKey) {
            $depModule = $this->findByKey($depKey);
            $installed = $tenant->hasModule($depKey);
            $canInstall = !$installed && $this->canInstall($depKey, $tenant);
            
            $dependencies[] = [
                'key' => $depKey,
                'name' => $depModule?->name ?? $depKey,
                'icon' => $depModule?->icon ?? 'bi-box',
                'required' => true,
                'installed' => $installed,
                'can_install' => $canInstall,
                'is_free' => $depModule?->is_free ?? true,
            ];
        }
        
        return $dependencies;
    }

    public function getDependents(string $moduleKey, Tenant $tenant): array
    {
        $dependents = [];
        
        // Get all modules that have this module as a dependency
        $modules = Module::whereJsonContains('dependencies', $moduleKey)->get();
        
        foreach ($modules as $module) {
            if ($tenant->hasModule($module->key)) {
                $dependents[] = [
                    'key' => $module->key,
                    'name' => $module->name,
                    'icon' => $module->icon,
                ];
            }
        }
        
        return $dependents;
    }

    public function getStats(string $moduleKey): array
    {
        $module = $this->findByKey($moduleKey);
        
        if (!$module) {
            return [];
        }
        
        return [
            'total_installs' => $module->tenantSubscriptions()->installed()->count(),
            'active_tenants' => $module->tenantSubscriptions()->active()->count(),
            'trial_installs' => $module->tenantSubscriptions()->inTrial()->count(),
            'recent_installs' => $module->tenantSubscriptions()
                ->where('installed_at', '>=', now()->subDays(30))
                ->count(),
            'recent_uninstalls' => $module->tenantSubscriptions()
                ->where('unsubscribed_at', '>=', now()->subDays(30))
                ->count(),
            'by_plan' => $this->getInstallsByPlan($moduleKey),
        ];
    }

    public function invalidateModule(string $key): void
    {
        // Base repository doesn't cache - override in decorator
    }

    public function invalidateTenant(int $tenantId): void
    {
        // Base repository doesn't cache - override in decorator
    }

    public function invalidatePlan(int $planId): void
    {
        // Base repository doesn't cache - override in decorator
    }

    /**
     * Check if tenant's plan allows installation of a module.
     */
    protected function planAllowsInstallation(string $moduleKey, Tenant $tenant): bool
    {
        $plan = $tenant->plan;
        
        if (!$plan) {
            // No plan = free tier, only free modules
            $module = $this->findByKey($moduleKey);
            return $module && $module->is_free;
        }
        
        // Check module_mode
        if ($plan->module_mode === 'whitelist') {
            return $plan->planModules()
                ->where('module_key', $moduleKey)
                ->where('is_included', true)
                ->exists();
        }
        
        if ($plan->module_mode === 'blacklist') {
            $excluded = $plan->planModules()
                ->where('module_key', $moduleKey)
                ->where('is_available', false)
                ->exists();
            return !$excluded;
        }
        
        // marketplace mode
        $planModule = $plan->planModules()
            ->where('module_key', $moduleKey)
            ->first();
        
        // If included in plan, can use
        if ($planModule && $planModule->is_included) {
            return true;
        }
        
        // Check if add-ons allowed
        if (!$plan->allow_addon_purchases) {
            return false;
        }
        
        // Check max_addons limit
        if ($plan->max_addons > 0) {
            $currentAddons = DB::table('tenant_modules')
                ->where('tenant_id', $tenant->id)
                ->where('is_enabled', true)
                ->count();
            
            if ($currentAddons >= $plan->max_addons) {
                return false;
            }
        }
        
        // Must be available as add-on
        return $planModule && $planModule->is_available;
    }

    /**
     * Get installation counts grouped by plan.
     */
    protected function getInstallsByPlan(string $moduleKey): array
    {
        return DB::table('tenant_module_subscriptions')
            ->join('tenants', 'tenant_module_subscriptions.tenant_id', '=', 'tenants.id')
            ->join('plans', 'tenants.plan_id', '=', 'plans.id')
            ->where('tenant_module_subscriptions.module_key', $moduleKey)
            ->where('tenant_module_subscriptions.status', 'active')
            ->select('plans.name', DB::raw('count(*) as count'))
            ->groupBy('plans.id', 'plans.name')
            ->pluck('count', 'name')
            ->toArray();
    }
}
