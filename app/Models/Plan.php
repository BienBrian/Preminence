<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'billing_cycle',
        'max_users',
        'max_sms_per_month',
        'max_storage_mb',
        'trial_days',
        'modules',
        'features',
        'is_active',
        'sort_order',
        // New marketplace fields
        'module_mode',
        'allow_addon_purchases',
        'max_addons',
        'allow_downgrades',
        'marketplace_settings',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'modules'     => 'array',
        'features'    => 'array',
        'is_active'   => 'boolean',
        // New marketplace casts
        'allow_addon_purchases' => 'boolean',
        'max_addons' => 'integer',
        'allow_downgrades' => 'boolean',
        'marketplace_settings' => 'array',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function planModules(): HasMany
    {
        return $this->hasMany(PlanModule::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function hasModule(string $module): bool
    {
        return (bool) ($this->modules[$module] ?? false);
    }

    public function hasFeature(string $feature): bool
    {
        return (bool) ($this->features[$feature] ?? false);
    }

    public function isFree(): bool
    {
        return $this->slug === 'free';
    }

    public function activeTenantsCount(): int
    {
        return $this->tenants()->whereIn('status', ['active', 'trial'])->count();
    }

    // ─── Marketplace Helpers ──────────────────────────────────────────────────

    /**
     * Check if this plan allows marketplace add-on purchases.
     */
    public function allowsAddons(): bool
    {
        return $this->allow_addon_purchases && $this->module_mode === 'marketplace';
    }

    /**
     * Get module configuration for this plan.
     */
    public function getModuleConfig(string $moduleKey): ?PlanModule
    {
        return $this->planModules()
            ->where('module_key', $moduleKey)
            ->first();
    }

    /**
     * Check if module is included in this plan.
     */
    public function isModuleIncluded(string $moduleKey): bool
    {
        return $this->planModules()
            ->where('module_key', $moduleKey)
            ->where('is_included', true)
            ->exists();
    }

    /**
     * Check if module is available as add-on for this plan.
     */
    public function isModuleAvailable(string $moduleKey): bool
    {
        return $this->planModules()
            ->where('module_key', $moduleKey)
            ->where('is_available', true)
            ->exists();
    }

    /**
     * Check if tenant can add more addons.
     */
    public function canAddAddon(int $currentAddonCount): bool
    {
        if ($this->max_addons === 0) {
            return true;
        }

        return $currentAddonCount < $this->max_addons;
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithMarketplace($query)
    {
        return $query->where('module_mode', 'marketplace');
    }
}
