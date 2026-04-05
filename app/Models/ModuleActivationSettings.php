<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Module Activation Settings
 * 
 * Controls how tenants can activate modules including self-service,
 * approval requirements, and plan-based auto-approval.
 */
class ModuleActivationSettings extends Model
{
    use HasFactory;

    protected $table = 'module_activation_settings';

    protected $fillable = [
        'module_key',
        'tenant_can_self_activate',
        'requires_superadmin_approval',
        'auto_approve_for_plans',
        'minimum_plan_tier',
        'allow_trial',
        'trial_days',
        'activation_messages',
    ];

    protected $casts = [
        'tenant_can_self_activate' => 'boolean',
        'requires_superadmin_approval' => 'boolean',
        'allow_trial' => 'boolean',
        'auto_approve_for_plans' => 'array',
        'activation_messages' => 'array',
    ];

    /**
     * Get the module these settings belong to.
     */
    public function module()
    {
        return $this->belongsTo(Module::class, 'module_key', 'key');
    }

    /**
     * Check if tenant can self-activate this module.
     */
    public function canSelfActivate(): bool
    {
        return $this->tenant_can_self_activate;
    }

    /**
     * Check if this module requires SuperAdmin approval.
     */
    public function requiresApproval(): bool
    {
        return $this->requires_superadmin_approval;
    }

    /**
     * Check if trial is allowed.
     */
    public function allowsTrial(): bool
    {
        return $this->allow_trial;
    }

    /**
     * Check if plan is eligible for auto-approval.
     */
    public function isAutoApprovedForPlan(string $planSlug): bool
    {
        return in_array($planSlug, $this->auto_approve_for_plans ?? []);
    }

    /**
     * Check if tenant meets minimum plan tier.
     */
    public function meetsPlanRequirement(?string $tenantPlan): bool
    {
        if (empty($this->minimum_plan_tier)) {
            return true; // No minimum required
        }
        
        // Simple tier comparison - could be enhanced with tier weights
        $tiers = ['basic' => 1, 'standard' => 2, 'premium' => 3, 'enterprise' => 4];
        $required = $tiers[$this->minimum_plan_tier] ?? 0;
        $current = $tiers[$tenantPlan] ?? 0;
        
        return $current >= $required;
    }

    /**
     * Get activation message for specific status.
     */
    public function getMessage(string $status, string $default = ''): string
    {
        return $this->activation_messages[$status] ?? $default;
    }

    /**
     * Scope: Self-activatable modules.
     */
    public function scopeSelfActivatable($query)
    {
        return $query->where('tenant_can_self_activate', true);
    }

    /**
     * Scope: Modules requiring approval.
     */
    public function scopeRequiresApproval($query)
    {
        return $query->where('requires_superadmin_approval', true);
    }

    /**
     * Get or create settings for a module.
     */
    public static function forModule(string $moduleKey): self
    {
        return self::firstOrCreate(
            ['module_key' => $moduleKey],
            [
                'tenant_can_self_activate' => false,
                'requires_superadmin_approval' => false,
                'allow_trial' => true,
                'trial_days' => 14,
            ]
        );
    }
}
