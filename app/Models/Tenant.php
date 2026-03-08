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
        'suspension_type',
        'suspension_reason',
        'suspension_details',
        'suspension_amount_due',
        'suspension_currency',
        'suspension_ends_at',
        'suspended_by',
    ];

    protected $casts = [
        'trial_ends_at'         => 'datetime',
        'subscription_ends_at'  => 'datetime',
        'setup_complete'        => 'boolean',
        'settings'              => 'array',
        'suspension_details'    => 'array',
        'suspension_amount_due' => 'decimal:2',
        'suspension_ends_at'    => 'datetime',
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

    /**
     * Get the most recent subscription (for eager loading compatibility)
     */
    public function subscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Subscription::class)->latest();
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
    
    /**
     * Get suspension display info for the error page.
     */
    public function getSuspensionInfo(): array
    {
        $types = [
            'financial' => [
                'title' => 'Account Suspended Due to Payment Issues',
                'description' => 'Your account has been suspended due to outstanding payment. Please settle the amount below to restore access.',
                'icon' => 'credit-card',
                'color' => 'warning',
            ],
            'terms_violation' => [
                'title' => 'Account Suspended - Terms of Service Violation',
                'description' => 'Your account has been suspended for violating our Terms of Service. Please contact our support team for assistance.',
                'icon' => 'shield-exclamation',
                'color' => 'danger',
            ],
            'admin_action' => [
                'title' => 'Account Temporarily Suspended',
                'description' => 'Your account has been temporarily suspended by an administrator. Please contact our support team for more information.',
                'icon' => 'lock',
                'color' => 'secondary',
            ],
        ];
        
        $type = $this->suspension_type ?? 'admin_action';
        $info = $types[$type] ?? $types['admin_action'];
        
        return [
            'type' => $type,
            'title' => $info['title'],
            'description' => $info['description'],
            'icon' => $info['icon'],
            'color' => $info['color'],
            'reason' => $this->suspension_reason,
            'amount_due' => $this->suspension_amount_due,
            'currency' => $this->suspension_currency ?? 'KES',
            'ends_at' => $this->suspension_ends_at,
            'is_financial' => $type === 'financial',
            'is_terms_violation' => $type === 'terms_violation',
            'is_admin_action' => $type === 'admin_action',
        ];
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
