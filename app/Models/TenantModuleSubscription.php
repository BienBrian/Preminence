<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TenantModuleSubscription extends Model
{
    use HasFactory;

    protected $table = 'tenant_module_subscriptions';

    protected $fillable = [
        'tenant_id', 'module_key', 'status', 'billing_type', 'price', 'currency',
        'idempotency_key', 'installed_at', 'trial_ends_at', 'next_billing_at',
        'last_billed_at', 'unsubscribed_at', 'suspended_at', 'suspension_reason',
        'installed_by', 'version_installed', 'installation_log', 'installation_error',
        'settings', 'limits', 'features_enabled', 'usage_count', 'usage_metrics',
        'last_used_at', 'cancellation_reason', 'cancellation_feedback', 'cancelled_by',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'installed_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'next_billing_at' => 'datetime',
        'last_billed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'suspended_at' => 'datetime',
        'installation_log' => 'array',
        'settings' => 'array',
        'limits' => 'array',
        'features_enabled' => 'array',
        'usage_metrics' => 'array',
        'last_used_at' => 'datetime',
        'cancellation_feedback' => 'array',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInstalled($query)
    {
        return $query->whereIn('status', ['active', 'suspended', 'paused']);
    }

    public function scopePendingBilling($query)
    {
        return $query->where('status', 'active')
            ->whereNotNull('next_billing_at')
            ->where('next_billing_at', '<=', now());
    }

    public function scopeInTrial($query)
    {
        return $query->where('billing_type', 'trial')
            ->where('trial_ends_at', '>', now());
    }

    public function scopeTrialExpired($query)
    {
        return $query->where('billing_type', 'trial')
            ->where('trial_ends_at', '<=', now());
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_key', 'key');
    }

    public function tenantModule(): HasOne
    {
        return $this->hasOne(TenantModule::class, 'subscription_id');
    }

    public function installer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'installed_by');
    }

    // Status Helpers
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInstalled(): bool
    {
        return in_array($this->status, ['active', 'suspended', 'paused']);
    }

    public function isInTrial(): bool
    {
        return $this->billing_type === 'trial' && $this->trial_ends_at?->isFuture();
    }

    public function trialExpired(): bool
    {
        return $this->billing_type === 'trial' && $this->trial_ends_at?->isPast();
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isRecurring(): bool
    {
        return in_array($this->billing_type, ['addon_monthly', 'addon_yearly']);
    }

    // Trial Management
    public function daysRemainingInTrial(): ?int
    {
        if (!$this->isInTrial()) {
            return null;
        }
        
        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    public function trialProgressPercent(): int
    {
        if (!$this->isInTrial()) {
            return 100;
        }
        
        $totalDays = $this->module?->trial_days ?? 14;
        $remaining = $this->daysRemainingInTrial();
        
        return (int) round((($totalDays - $remaining) / $totalDays) * 100);
    }

    // Billing Helpers
    public function getBillingPeriodLabel(): string
    {
        return match($this->billing_type) {
            'plan_included' => 'Included in Plan',
            'addon_monthly' => 'Monthly',
            'addon_yearly' => 'Yearly',
            'one_time' => 'One-time Purchase',
            'trial' => 'Trial',
            'complimentary' => 'Complimentary',
            default => ucfirst(str_replace('_', ' ', $this->billing_type)),
        };
    }

    public function getNextBillingAmount(): ?float
    {
        if (!$this->isRecurring()) {
            return null;
        }
        
        return $this->price;
    }

    public function recordUsage(string $metric, int $increment = 1): void
    {
        $this->increment('usage_count', $increment);
        
        $metrics = $this->usage_metrics ?? [];
        $metrics[$metric] = ($metrics[$metric] ?? 0) + $increment;
        
        $this->update([
            'usage_metrics' => $metrics,
            'last_used_at' => now(),
        ]);
    }

    // Installation Log
    public function logInstallationStep(string $step, string $status, ?string $message = null): void
    {
        $log = $this->installation_log ?? [];
        $log[] = [
            'step' => $step,
            'status' => $status,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ];
        
        $this->update(['installation_log' => $log]);
    }

    public function getInstallationProgress(): int
    {
        if ($this->status === 'active') {
            return 100;
        }
        
        if ($this->status === 'failed') {
            return 0;
        }
        
        $log = $this->installation_log ?? [];
        $totalSteps = 5; // Discover, Validate, Migrate, Seed, Activate
        $completedSteps = count(array_filter($log, fn($l) => ($l['status'] ?? '') === 'complete'));
        
        return (int) round(($completedSteps / $totalSteps) * 100);
    }
}
