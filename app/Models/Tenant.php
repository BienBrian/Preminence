<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'logo',
        'status',
        'plan_id',
        'trial_ends_at',
        'subscription_ends_at',
        'owner_user_id',
        'setup_complete',
        'grace_period_days',
        'settings',
    ];

    protected $casts = [
        'trial_ends_at'         => 'datetime',
        'subscription_ends_at'  => 'datetime',
        'setup_complete'        => 'boolean',
        'settings'              => 'array',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->latest()
            ->first();
    }

    public function modules(): HasMany
    {
        return $this->hasMany(TenantModule::class);
    }

    // ─── Status Helpers ───────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial']);
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at?->isFuture();
    }

    public function trialExpired(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at?->isPast();
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isWithinGracePeriod(): bool
    {
        if (!$this->subscription_ends_at) return false;
        return $this->subscription_ends_at->addDays($this->grace_period_days)->isFuture();
    }

    // ─── Module Helpers ───────────────────────────────────────────────────────

    public function hasModule(string $module): bool
    {
        return $this->modules()
            ->where('module', $module)
            ->where('is_enabled', true)
            ->exists();
    }

    public function enableModule(string $module, bool $byAdmin = false, ?int $adminId = null): void
    {
        $this->modules()->updateOrCreate(
            ['module' => $module],
            [
                'is_enabled'       => true,
                'override_by_admin' => $byAdmin,
                'overridden_by'    => $adminId,
                'enabled_at'       => now(),
                'disabled_at'      => null,
            ]
        );
        // Flush the module cache
        cache()->forget("tenant_{$this->id}_module_{$module}");
    }

    public function disableModule(string $module): void
    {
        $this->modules()->where('module', $module)->update([
            'is_enabled'  => false,
            'disabled_at' => now(),
        ]);
        cache()->forget("tenant_{$this->id}_module_{$module}");
    }
}
