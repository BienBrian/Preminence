<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantModule extends Model
{
    protected $fillable = [
        'tenant_id',
        'module',
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

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
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
}
