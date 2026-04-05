<?php

namespace App\Services;

use App\Models\ModulePermission;
use Illuminate\Support\Facades\Cache;

/**
 * Module Permission Service
 * 
 * Manages permissions that are tied to modules. Only shows permissions
 * for modules that are active for the current tenant.
 */
class ModulePermissionService
{
    /**
     * Cache key for active module permissions
     */
    private const CACHE_KEY = 'active_module_permissions';

    /**
     * Cache duration in minutes
     */
    private const CACHE_DURATION = 60;

    /**
     * Core permissions that are always available (not tied to modules)
     */
    private const CORE_PERMISSIONS = [
        'View Users',
        'Add Users',
        'Edit Users',
        'Delete Users',
        'View Roles',
        'Add Roles',
        'Edit Roles',
        'View People',
        'Edit People',
        'Add People',
        'View Website Settings',
        'Edit Website Settings',
        'Add Website Settings',
        'View Finances',
        'Add Finances',
        'Edit Finances',
        'View Events & Notices',
        'Add Events & Notices',
        'Edit Events & Notices',
        'View Children Checkin',
        'Add Children Checkin',
        'Edit Children Checkin',
        'View Spiritual',
        'Add Spiritual',
        'Edit Spiritual',
        'View Shop',
        'Add Shop',
        'Edit Shop',
        'View Communication',
        'Add Communication',
        'Edit Communication',
        'View Articles',
        'Add Articles',
        'Edit Articles',
        'View Payment Settings',
        'Add Payment Settings',
        'Edit Payment Settings',
        'View Settings',
        'Edit Settings',
        'View Dashboard',
    ];

    /**
     * Get all permissions that should be visible based on active modules
     */
    public function getVisiblePermissions(): array
    {
        $tenantId = config('app.tenant_id');
        
        if (!$tenantId) {
            // Superadmin context - show all permissions
            return $this->getAllPermissions();
        }

        return Cache::remember(
            self::CACHE_KEY . '_' . $tenantId,
            now()->addMinutes(self::CACHE_DURATION),
            fn () => $this->calculateVisiblePermissions($tenantId)
        );
    }

    /**
     * Calculate which permissions should be visible for a tenant
     */
    private function calculateVisiblePermissions(int $tenantId): array
    {
        // Get active modules for this tenant
        $activeModules = $this->getActiveModuleKeys($tenantId);
        
        // Get module-specific permissions for active modules
        $modulePermissionNames = ModulePermission::whereIn('module_key', $activeModules)
            ->where('is_active', true)
            ->pluck('name')
            ->toArray();

        // Combine core permissions with module-specific permissions
        return array_unique(array_merge(self::CORE_PERMISSIONS, $modulePermissionNames));
    }

    /**
     * Get active module keys for a tenant
     */
    private function getActiveModuleKeys(int $tenantId): array
    {
        return \App\Models\TenantModule::where('tenant_id', $tenantId)
            ->where('is_enabled', true)
            ->pluck('module')
            ->toArray();
    }

    /**
     * Get all permissions (for superadmin)
     */
    private function getAllPermissions(): array
    {
        $modulePermissions = ModulePermission::where('is_active', true)
            ->pluck('name')
            ->toArray();

        return array_unique(array_merge(self::CORE_PERMISSIONS, $modulePermissions));
    }

    /**
     * Clear the permission cache for a tenant
     */
    public function clearCache(?int $tenantId = null): void
    {
        $tenantId = $tenantId ?? config('app.tenant_id');
        
        if ($tenantId) {
            Cache::forget(self::CACHE_KEY . '_' . $tenantId);
        }
    }

    /**
     * Check if a permission is tied to a module
     */
    public function isModulePermission(string $permissionName): bool
    {
        return ModulePermission::where('name', $permissionName)->exists();
    }

    /**
     * Get the module key for a permission
     */
    public function getModuleForPermission(string $permissionName): ?string
    {
        $modulePermission = ModulePermission::where('name', $permissionName)->first();
        
        return $modulePermission?->module_key;
    }

    /**
     * Get all permissions for a specific module
     */
    public function getPermissionsForModule(string $moduleKey): array
    {
        return ModulePermission::where('module_key', $moduleKey)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }

    /**
     * Check if a module is active for the current tenant
     */
    public function isModuleActive(string $moduleKey): bool
    {
        $tenantId = config('app.tenant_id');
        
        if (!$tenantId) {
            return true; // Superadmin context
        }

        return \App\Models\TenantModule::where('tenant_id', $tenantId)
            ->where('module', $moduleKey)
            ->where('is_enabled', true)
            ->exists();
    }

    /**
     * Filter a permission query to only show visible permissions
     */
    public function filterPermissionQuery($query)
    {
        $visiblePermissions = $this->getVisiblePermissions();
        
        return $query->whereIn('name', $visiblePermissions);
    }
}
