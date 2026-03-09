<?php

namespace App\Services;

use App\Models\TenantModule;
use Illuminate\Support\Facades\Cache;

/**
 * ModuleService
 *
 * Central service for checking whether a feature module is enabled for the
 * current tenant. Results are cached in Redis for 5 minutes to avoid hitting
 * the DB on every request.
 *
 * Usage:
 *   app(ModuleService::class)->isEnabled('finance')
 *   module('finance')   ← global helper shortcut
 */
class ModuleService
{
    /**
     * All known module keys. This is the single source of truth.
     * Must match the keys in PlansSeeder and tenant_modules.module column.
     */
    public const MODULES = [
        'people',
        'attendance',
        'finance',
        'mpesa',
        'sms',
        'email',
        'events',
        'spiritual',
        'website',
        'shop',
        'media',
        'reports',
        'discipleship',
        'api_access',
        'links',
    ];

    /**
     * Check if a module is enabled for the current tenant.
     * Returns true if no tenant context is set (e.g. superadmin panel, CLI).
     */
    public function isEnabled(string $module): bool
    {
        $tenantId = config('app.tenant_id');

        // No tenant context → allow (superadmin, artisan commands, API without tenant)
        if (!$tenantId) {
            return true;
        }

        return Cache::remember(
            "tenant_{$tenantId}_module_{$module}",
            now()->addMinutes(5),
            fn () => TenantModule::where('tenant_id', $tenantId)
                ->where('module', $module)
                ->where('is_enabled', true)
                ->exists()
        );
    }

    /**
     * Flush the cached module status for a specific module (or all modules)
     * for the given tenant. Call this after enabling/disabling a module.
     */
    public function flushCache(int $tenantId, ?string $module = null): void
    {
        if ($module) {
            Cache::forget("tenant_{$tenantId}_module_{$module}");
        } else {
            foreach (self::MODULES as $m) {
                Cache::forget("tenant_{$tenantId}_module_{$m}");
            }
        }
    }

    /**
     * Get all enabled module keys for the current tenant.
     */
    public function enabledModules(?int $tenantId = null): array
    {
        $tenantId = $tenantId ?? config('app.tenant_id');

        if (!$tenantId) return self::MODULES;

        return TenantModule::where('tenant_id', $tenantId)
            ->where('is_enabled', true)
            ->pluck('module')
            ->toArray();
    }
}
