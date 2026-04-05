<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantModule extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'module',
        'subscription_id',
        'is_enabled',
        'override_by_admin',
        'overridden_by',
        'enabled_at',
        'disabled_at',
    ];

    protected $casts = [
        'is_enabled'        => 'boolean',
        'override_by_admin' => 'boolean',
        'enabled_at'        => 'datetime',
        'disabled_at'       => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantModuleSubscription::class, 'subscription_id');
    }

    public function overriddenBy(): BelongsTo
    {
        return $this->belongsTo(SuperAdmin::class, 'overridden_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeAdminOverrides($query)
    {
        return $query->where('override_by_admin', true);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Check if this module was installed via marketplace (add-on purchase).
     */
    public function isAddon(): bool
    {
        return $this->subscription?->isRecurring() ?? false;
    }

    /**
     * Check if this is a plan-included module.
     */
    public function isPlanIncluded(): bool
    {
        return $this->subscription?->billing_type === 'plan_included';
    }

    /**
     * Check if installed by admin override.
     */
    public function isAdminOverride(): bool
    {
        return $this->override_by_admin;
    }
}
