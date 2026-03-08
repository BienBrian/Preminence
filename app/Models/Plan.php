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
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'modules'     => 'array',
        'features'    => 'array',
        'is_active'   => 'boolean',
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

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
