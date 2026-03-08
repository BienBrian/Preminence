<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformAuditLog extends Model
{
    protected $table = 'platform_audit_log';  // Singular table name
    
    public $timestamps = false;  // Only has created_at (immutable log)

    protected $fillable = [
        'tenant_id',
        'super_admin_id',
        'user_id',
        'action',
        'details',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'details'    => 'array',
        'created_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function superAdmin(): BelongsTo
    {
        return $this->belongsTo(SuperAdmin::class);
    }

    // ─── Static Factory ───────────────────────────────────────────────────────

    /**
     * Record a platform-level action. Use this everywhere instead of
     * creating PlatformAuditLog directly.
     *
     * @param string      $action    e.g. "tenant.created", "module.toggled"
     * @param array       $details   before/after or arbitrary context
     * @param int|null    $tenantId
     * @param int|null    $superAdminId
     * @param int|null    $userId    tenant user (if impersonating)
     */
    public static function record(
        string $action,
        array $details = [],
        ?int $tenantId = null,
        ?int $superAdminId = null,
        ?int $userId = null
    ): void {
        static::create([
            'action'         => $action,
            'details'        => $details,
            'tenant_id'      => $tenantId ?? config('app.tenant_id'),
            'super_admin_id' => $superAdminId,
            'user_id'        => $userId,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
            'created_at'     => now(),
        ]);
    }
}
