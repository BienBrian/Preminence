<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantModulePermissionGrant extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_module_subscription_id',
        'module_permission_id',
        'is_granted',
        'expires_at',
        'conditions',
    ];

    protected $casts = [
        'is_granted' => 'boolean',
        'expires_at' => 'datetime',
        'conditions' => 'array',
    ];

    // Relationships
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantModuleSubscription::class, 'tenant_module_subscription_id');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(ModulePermission::class, 'module_permission_id');
    }

    // Scopes
    public function scopeGranted($query)
    {
        return $query->where('is_granted', true);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeValid($query)
    {
        return $query->granted()->notExpired();
    }

    // Helpers
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return $this->is_granted && !$this->isExpired();
    }
}
