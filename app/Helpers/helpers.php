<?php

use App\Models\Tenant;
use App\Services\ModuleService;

if (!function_exists('tenant')) {
    /**
     * Get the current tenant instance from the service container.
     *
     * Returns null when there is no tenant context (superadmin panel, CLI, local dev
     * without a subdomain, or Mpesa callback routes before tenant is resolved).
     *
     * @return Tenant|null
     */
    function tenant(): ?Tenant
    {
        try {
            return app()->bound('tenant') ? app('tenant') : null;
        } catch (\Throwable) {
            return null;
        }
    }
}

if (!function_exists('tenant_id')) {
    /**
     * Get the current tenant ID, or null if no tenant context.
     */
    function tenant_id(): ?int
    {
        return config('app.tenant_id') ?: null;
    }
}

if (!function_exists('module')) {
    /**
     * Check if a feature module is enabled for the current tenant.
     *
     * Usage in Blade:
     *   @if(module('finance'))
     *       <li><a href="/dashboard/finance">Finance</a></li>
     *   @endif
     *
     * Usage in PHP:
     *   if (module('mpesa')) { ... }
     *
     * @param  string $module  One of the keys in ModuleService::MODULES
     * @return bool
     */
    function module(string $module): bool
    {
        return app(ModuleService::class)->isEnabled($module);
    }
}

if (!function_exists('tenant_storage_path')) {
    /**
     * Get the storage path for the current tenant, with optional sub-path.
     *
     * e.g. tenant_storage_path('profile_images/avatar.jpg')
     *   → storage/app/tenants/1/profile_images/avatar.jpg
     *
     * @param  string $path Optional sub-path within the tenant's storage directory
     * @return string Absolute path
     */
    function tenant_storage_path(string $path = ''): string
    {
        $tenantId = config('app.tenant_id', 'shared');
        $base = storage_path("app/tenants/{$tenantId}");

        if ($path) {
            return $base . '/' . ltrim($path, '/');
        }

        return $base;
    }
}

if (!function_exists('is_superadmin')) {
    /**
     * Check if the current session is an authenticated superadmin.
     * (Will be properly wired in Phase 8 when the superadmin guard is added.)
     */
    function is_superadmin(): bool
    {
        try {
            return auth('superadmin')->check();
        } catch (\Throwable) {
            return false;
        }
    }
}

if (!function_exists('tenant_storage_url')) {
    /**
     * Get the URL for a tenant-specific stored asset.
     *
     * Relies on the `tenant-assets` route defined in web.php.
     *
     * e.g. tenant_storage_url('profile_images/avatar.jpg')
     *   → https://yourapp.com/tenant-assets/profile_images/avatar.jpg
     *
     * @param  string $path Sub-path within the tenant's storage directory
     * @return string
     */
    function tenant_storage_url(string $path = ''): string
    {
        return url('tenant-assets/' . ltrim($path, '/'));
    }
}

