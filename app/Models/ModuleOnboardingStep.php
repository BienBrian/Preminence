<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Module Onboarding Step
 * 
 * Represents an individual step in a multi-step onboarding flow.
 * Supports various content types: info, form, video, document upload.
 */
class ModuleOnboardingStep extends Model
{
    use HasFactory;

    protected $table = 'module_onboarding_steps';

    protected $fillable = [
        'module_onboarding_config_id',
        'step_number',
        'step_key',
        'title',
        'description',
        'content',
        'content_type',
        'video_url',
        'image_url',
        'icon',
        'form_schema',
        'document_config',
        'is_required',
        'is_skippable',
        'allow_back',
        'show_conditions',
        'next_step_logic',
        'estimated_minutes',
        'is_active',
    ];

    protected $casts = [
        'form_schema' => 'array',
        'document_config' => 'array',
        'show_conditions' => 'array',
        'next_step_logic' => 'array',
        'is_required' => 'boolean',
        'is_skippable' => 'boolean',
        'allow_back' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the onboarding config this step belongs to.
     */
    public function config(): BelongsTo
    {
        return $this->belongsTo(ModuleOnboardingConfig::class, 'module_onboarding_config_id');
    }

    /**
     * Scope: Active steps only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Ordered by step number.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('step_number', 'asc');
    }

    /**
     * Check if this step is an info step.
     */
    public function isInfo(): bool
    {
        return $this->content_type === 'info';
    }

    /**
     * Check if this step contains a form.
     */
    public function isForm(): bool
    {
        return $this->content_type === 'form';
    }

    /**
     * Check if this step is a video tutorial.
     */
    public function isVideo(): bool
    {
        return $this->content_type === 'video';
    }

    /**
     * Check if this step requires document upload.
     */
    public function isDocumentUpload(): bool
    {
        return $this->content_type === 'document_upload';
    }

    /**
     * Check if this step is a confirmation step.
     */
    public function isConfirmation(): bool
    {
        return $this->content_type === 'confirmation';
    }

    /**
     * Check if this is the final completion step.
     */
    public function isCompletion(): bool
    {
        return $this->content_type === 'completion';
    }

    /**
     * Get the next step based on conditional logic.
     */
    public function getNextStep(?array $formData = null): ?self
    {
        // If no conditional logic, return next sequential step
        if (empty($this->next_step_logic)) {
            return $this->config->steps()
                ->where('step_number', '>', $this->step_number)
                ->where('is_active', true)
                ->orderBy('step_number')
                ->first();
        }

        // TODO: Implement conditional logic evaluation
        // For now, return next sequential step
        return $this->config->steps()
            ->where('step_number', '>', $this->step_number)
            ->where('is_active', true)
            ->orderBy('step_number')
            ->first();
    }

    /**
     * Check if step should be shown based on conditions.
     */
    public function shouldShow(?array $previousData = []): bool
    {
        if (empty($this->show_conditions)) {
            return true;
        }

        // TODO: Implement condition evaluation
        // For now, always show
        return true;
    }

    /**
     * Get estimated time label.
     */
    public function getEstimatedTimeLabel(): ?string
    {
        if (!$this->estimated_minutes) {
            return null;
        }

        if ($this->estimated_minutes < 1) {
            return '< 1 min';
        }

        return $this->estimated_minutes . ' min';
    }
}
