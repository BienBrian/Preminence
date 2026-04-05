<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id', 'module_key', 'is_included', 'is_available', 'is_featured',
        'price_monthly_override', 'price_yearly_override', 'setup_fee_override',
        'limits_override', 'trial_days', 'extend_existing_trial',
        'plan_highlights', 'plan_badge', 'configuration',
    ];

    protected $casts = [
        'is_included' => 'boolean',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'price_monthly_override' => 'decimal:2',
        'price_yearly_override' => 'decimal:2',
        'setup_fee_override' => 'decimal:2',
        'limits_override' => 'array',
        'trial_days' => 'integer',
        'extend_existing_trial' => 'boolean',
        'plan_highlights' => 'array',
        'configuration' => 'array',
    ];

    // Relationships
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_key', 'key');
    }

    // Helpers
    public function getPrice(string $billingCycle = 'monthly'): ?float
    {
        $override = $billingCycle === 'yearly' 
            ? $this->price_yearly_override 
            : $this->price_monthly_override;
        
        if ($override !== null) {
            return $override;
        }
        
        return $this->module?->getPrice($billingCycle);
    }

    public function getSetupFee(): float
    {
        return $this->setup_fee_override ?? $this->module?->setup_fee ?? 0;
    }

    public function getLimits(): array
    {
        $moduleLimits = $this->module?->default_limits ?? [];
        $overrideLimits = $this->limits_override ?? [];
        
        return array_merge($moduleLimits, $overrideLimits);
    }

    public function getTrialDays(): int
    {
        return $this->trial_days ?? $this->module?->trial_days ?? 0;
    }

    public function isAccessible(): bool
    {
        return $this->is_included || $this->is_available;
    }

    public function getEffectiveMonthlyPrice(): ?float
    {
        if ($this->is_included) {
            return 0;
        }
        
        return $this->getPrice('monthly');
    }
}
