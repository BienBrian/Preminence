<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Module Onboarding Configuration
 * 
 * Defines the onboarding flow for each module including KYC requirements,
 * document uploads, tutorial content, setup wizards, and contextual help.
 */
class ModuleOnboardingConfig extends Model
{
    use HasFactory;

    protected $table = 'module_onboarding_configs';

    protected $fillable = [
        'module_key',
        'onboarding_type',
        'requires_approval',
        'required_documents',
        'kyc_form_schema',
        'tutorial_content',
        'network_participation_enabled',
        'approval_instructions',
        // New fields
        'setup_wizard_schema',
        'contextual_help_enabled',
        'contextual_help_content',
        'preview_enabled',
        'estimated_setup_time_minutes',
        'welcome_message',
        'completion_message',
        'auto_redirect_to_module',
        'video_url',
        'documentation_url',
        'template_key',
        'is_configured',
        'configured_at',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'network_participation_enabled' => 'boolean',
        'required_documents' => 'array',
        'kyc_form_schema' => 'array',
        'tutorial_content' => 'array',
        // New casts
        'setup_wizard_schema' => 'array',
        'contextual_help_enabled' => 'boolean',
        'contextual_help_content' => 'array',
        'preview_enabled' => 'boolean',
        'auto_redirect_to_module' => 'boolean',
        'is_configured' => 'boolean',
        'configured_at' => 'datetime',
    ];

    /**
     * Get the module this config belongs to.
     */
    public function module()
    {
        return $this->belongsTo(Module::class, 'module_key', 'key');
    }

    /**
     * Get all onboarding submissions for this module.
     */
    public function submissions()
    {
        return $this->hasMany(TenantModuleOnboarding::class, 'module_key', 'module_key');
    }

    /**
     * Get all onboarding steps for this module (for complex wizards).
     */
    public function steps(): HasMany
    {
        return $this->hasMany(ModuleOnboardingStep::class, 'module_onboarding_config_id')
            ->orderBy('step_number');
    }

    /**
     * Get active steps only.
     */
    public function activeSteps(): HasMany
    {
        return $this->steps()->where('is_active', true);
    }

    /**
     * Check if this module requires KYC onboarding.
     */
    public function isKyc(): bool
    {
        return $this->onboarding_type === 'kyc';
    }

    /**
     * Check if this module requires guided setup.
     */
    public function isGuided(): bool
    {
        return $this->onboarding_type === 'guided';
    }

    /**
     * Check if this module uses a setup wizard.
     */
    public function isSetupWizard(): bool
    {
        return $this->onboarding_type === 'setup_wizard';
    }

    /**
     * Check if this module has instant activation (no onboarding).
     */
    public function isInstant(): bool
    {
        return in_array($this->onboarding_type, ['none', 'instant']);
    }

    /**
     * Check if this module requires any form of onboarding.
     */
    public function requiresOnboarding(): bool
    {
        return !$this->isInstant();
    }

    /**
     * Get required documents as associative array.
     */
    public function getDocumentsList(): array
    {
        return $this->required_documents ?? [];
    }

    /**
     * Get tutorial steps.
     */
    public function getTutorialSteps(): array
    {
        return $this->tutorial_content['steps'] ?? [];
    }

    /**
     * Get contextual help items.
     */
    public function getContextualHelp(): array
    {
        return $this->contextual_help_content ?? [];
    }

    /**
     * Get setup wizard schema.
     */
    public function getWizardSchema(): array
    {
        return $this->setup_wizard_schema ?? [];
    }

    /**
     * Get estimated setup time label.
     */
    public function getEstimatedTimeLabel(): ?string
    {
        if (!$this->estimated_setup_time_minutes) {
            // Calculate from steps if available
            $totalMinutes = $this->steps->sum('estimated_minutes');
            if ($totalMinutes > 0) {
                return $totalMinutes . ' min';
            }
            return null;
        }

        if ($this->estimated_setup_time_minutes < 1) {
            return '< 1 min';
        }

        return $this->estimated_setup_time_minutes . ' min';
    }

    /**
     * Mark as configured.
     */
    public function markConfigured(): void
    {
        $this->update([
            'is_configured' => true,
            'configured_at' => now(),
        ]);
    }

    /**
     * Get total number of steps.
     */
    public function getTotalSteps(): int
    {
        if ($this->isGuided()) {
            return count($this->getTutorialSteps());
        }

        if ($this->isSetupWizard() || $this->isKyc()) {
            return $this->steps()->where('is_active', true)->count();
        }

        return 0;
    }

    /**
     * Scope: Only configured onboarding.
     */
    public function scopeConfigured($query)
    {
        return $query->where('is_configured', true);
    }

    /**
     * Scope: By onboarding type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('onboarding_type', $type);
    }

    /**
     * Scope: With contextual help enabled.
     */
    public function scopeWithHelp($query)
    {
        return $query->where('contextual_help_enabled', true);
    }
}
