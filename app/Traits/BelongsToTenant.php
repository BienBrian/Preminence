<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * BelongsToTenant Trait
 *
 * Apply this trait to every Eloquent model that belongs to a tenant.
 * It automatically:
 *   1. Adds a global query scope so ALL queries are filtered by the current tenant
 *   2. Auto-sets tenant_id on model creation so no controller code gets missed
 *
 * Usage:
 *   class Contact extends Model {
 *       use BelongsToTenant;
 *   }
 *
 * Skip the scope for superadmin queries (reports, platform monitoring):
 *   Contact::withoutTenantScope()->get();    // skip global scope
 *   Contact::withoutGlobalScopes()->get();   // skip ALL scopes
 */
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        // ── Global read scope ─────────────────────────────────────────────────
        static::addGlobalScope('tenant', function (Builder $query) {
            $tenantId = config('app.tenant_id');

            if ($tenantId) {
                // Use the table name to avoid ambiguous column errors on joins
                $query->where($query->getModel()->getTable() . '.tenant_id', $tenantId);
            }
        });

        // ── Auto-fill tenant_id on create ─────────────────────────────────────
        static::creating(function (self $model) {
            if (empty($model->tenant_id) && $tenantId = config('app.tenant_id')) {
                $model->tenant_id = $tenantId;
            }
        });
    }

    /**
     * Remove the tenant global scope for this query only.
     * Use this in superadmin controllers or cross-tenant reporting.
     *
     * Example:
     *   User::withoutTenantScope()->where('email', $email)->first();
     */
    public static function withoutTenantScope(): Builder
    {
        return static::withoutGlobalScope('tenant');
    }

    /**
     * Scope to explicitly target a specific tenant, bypassing the current
     * request tenant context. Useful in queue jobs that carry their own tenant ID.
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->withoutGlobalScope('tenant')
            ->where($this->getTable() . '.tenant_id', $tenantId);
    }
}
