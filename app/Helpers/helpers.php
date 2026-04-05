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

// =============================================================================
// NEW HELPER FUNCTIONS ADDED FOR PISTI PLATFORM
// =============================================================================

if (!function_exists('is_dev_mode')) {
    /**
     * Check if running in development mode.
     *
     * @return bool
     */
    function is_dev_mode(): bool
    {
        return config('pisti.env') === 'dev';
    }
}

if (!function_exists('is_staging_mode')) {
    /**
     * Check if running in staging mode.
     *
     * @return bool
     */
    function is_staging_mode(): bool
    {
        return config('pisti.env') === 'staging';
    }
}

if (!function_exists('is_production_mode')) {
    /**
     * Check if running in production mode.
     *
     * @return bool
     */
    function is_production_mode(): bool
    {
        return config('pisti.env') === 'production';
    }
}

if (!function_exists('is_local_environment')) {
    /**
     * Check if running in local/development environment.
     * Alias for is_dev_mode() for backward compatibility.
     *
     * @return bool
     */
    function is_local_environment(): bool
    {
        return is_dev_mode();
    }
}

if (!function_exists('is_subdomain_mode')) {
    /**
     * Check if superadmin should use subdomain-based routing.
     *
     * @return bool
     */
    function is_subdomain_mode(): bool
    {
        $mode = config('pisti.superadmin_mode');

        // Explicit path mode
        if ($mode === 'path') {
            return false;
        }

        // Explicit subdomain mode
        if ($mode === 'subdomain') {
            return true;
        }

        // Auto mode: use subdomain for staging and production
        return !is_dev_mode();
    }
}

if (!function_exists('is_path_mode')) {
    /**
     * Check if superadmin should use path-based routing.
     *
     * @return bool
     */
    function is_path_mode(): bool
    {
        return !is_subdomain_mode();
    }
}

if (!function_exists('should_force_https')) {
    /**
     * Check if HTTPS should be forced.
     *
     * @return bool
     */
    function should_force_https(): bool
    {
        $forceHttps = config('pisti.force_https');

        // Explicit true/false
        if (is_bool($forceHttps)) {
            return $forceHttps;
        }

        // Auto mode: force HTTPS in staging and production
        return !is_dev_mode();
    }
}

if (!function_exists('feature_enabled')) {
    /**
     * Check if a specific feature is enabled.
     *
     * @param string $feature
     * @return bool
     */
    function feature_enabled(string $feature): bool
    {
        $features = config('pisti.features', []);
        return in_array($feature, $features);
    }
}

if (!function_exists('reserved_subdomains')) {
    /**
     * Get the list of reserved subdomains.
     *
     * @return array
     */
    function reserved_subdomains(): array
    {
        return config('pisti.tenant.reserved_subdomains', [
            'www', 'admin', 'api', 'staging', 'horizon',
            'superadmin', 'mail', 'ftp', 'smtp', 'pop',
            'imap', 'webmail', 'webdisk', 'cpanel', 'whm',
            'ns1', 'ns2',
        ]);
    }
}

if (!function_exists('pisti_platform_domain')) {
    /**
     * Get the platform domain.
     *
     * @return string
     */
    function pisti_platform_domain(): string
    {
        return config('pisti.platform_domain', 'happychurchruiru.org');
    }
}

if (!function_exists('pisti_asset')) {
    /**
     * Generate an asset URL with proper handling for HTTPS.
     *
     * @param string $path
     * @param bool|null $secure
     * @return string
     */
    function pisti_asset(string $path, ?bool $secure = null): string
    {
        // If HTTPS is forced, always use secure
        if (should_force_https()) {
            return asset($path, true);
        }

        return asset($path, $secure);
    }
}

if (!function_exists('pisti_config')) {
    /**
     * Get a Pisti configuration value.
     *
     * @param string $key Dot-notation key (e.g., 'tenant.default_id')
     * @param mixed $default
     * @return mixed
     */
    function pisti_config(string $key, mixed $default = null): mixed
    {
        return config("pisti.{$key}", $default);
    }
}

if (!function_exists('module_enabled')) {
    /**
     * Alias for module() function.
     *
     * @param string $module The module key
     * @return bool
     */
    function module_enabled(string $module): bool
    {
        return module($module);
    }
}

if (!function_exists('rate_limit_for')) {
    /**
     * Get rate limit for a specific action.
     *
     * @param string $action
     * @param int $default
     * @return int
     */
    function rate_limit_for(string $action, int $default = 60): int
    {
        return config("pisti.rate_limits.{$action}", $default);
    }
}

if (!function_exists('superadmin_path_prefix')) {
    /**
     * Get the superadmin path prefix.
     *
     * @return string
     */
    function superadmin_path_prefix(): string
    {
        return config('pisti.superadmin.path_prefix', 'superadmin');
    }
}

if (!function_exists('superadmin_subdomain')) {
    /**
     * Get the superadmin subdomain.
     *
     * @return string
     */
    function superadmin_subdomain(): string
    {
        return config('pisti.superadmin.subdomain', 'superadmin');
    }
}

if (!function_exists('has_legacy_permission')) {
    /**
     * Check if user has a legacy permission (mapped to Spatie Permission).
     * 
     * This function bridges the old permission system to the new Spatie system.
     * Old permissions were stored in 'permissions' table with user_id column.
     * New permissions use Spatie with View/Add/Edit structure.
     *
     * @param string $permission Legacy permission name (e.g., 'dashboard', 'websites', 'finances')
     * @return bool
     */
    function has_legacy_permission(string $permission): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        // Role 1 is admin - has all permissions
        if ($user->role == 1) {
            return true;
        }
        
        // Map legacy permissions to Spatie permissions
        $permissionMap = [
            'dashboard' => ['View Dashboard', 'View Users', 'View People', 'View Finances', 'View Articles', 'View Events & Notices', 'View Spiritual', 'View Shop', 'View Website Settings', 'View Communication'],
            'websites' => ['View Website Settings', 'Edit Website Settings', 'Add Articles', 'Edit Articles', 'View Gallery', 'Edit Gallery'],
            'finances' => ['View Finances', 'Add Finances', 'Edit Finances'],
            'spiritual' => ['View Spiritual', 'Add Spiritual', 'Edit Spiritual'],
            'events' => ['View Events & Notices', 'Add Events & Notices', 'Edit Events & Notices'],
            'shop' => ['View Shop', 'Add Shop', 'Edit Shop'],
            'communication' => ['View Communication', 'Add Communication', 'Edit Communication'],
            'users' => ['View Users', 'Add Users', 'Edit Users', 'View People', 'Add People', 'Edit People'],
            'articles' => ['View Articles', 'Add Articles', 'Edit Articles'],
            'testimonials' => ['View Spiritual', 'Edit Spiritual'],
            'checkin' => ['View Children Checkin', 'Add Children Checkin', 'Edit Children Checkin'],
        ];
        
        $spatiePermissions = $permissionMap[$permission] ?? [];
        
        if (empty($spatiePermissions)) {
            return false;
        }
        
        // Check if user has any of the mapped permissions
        foreach ($spatiePermissions as $spatiePermission) {
            if ($user->can($spatiePermission)) {
                return true;
            }
        }
        
        return false;
    }
}
