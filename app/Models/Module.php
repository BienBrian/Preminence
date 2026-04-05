<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'key', 'name', 'slug', 'description', 'short_description',
        'category', 'tags', 'version', 'min_platform_version', 'max_platform_version',
        'dependencies', 'conflicts', 'is_free', 'price_monthly', 'price_yearly',
        'setup_fee', 'billing_model', 'features', 'default_limits',
        'icon', 'screenshots', 'documentation_url', 'video_url', 'highlights',
        'migration_path', 'seeder_class', 'install_hooks', 'estimated_install_time_seconds',
        'is_active', 'is_public', 'requires_approval', 'approval_level',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'tags' => 'array',
        'dependencies' => 'array',
        'conflicts' => 'array',
        'is_free' => 'boolean',
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'features' => 'array',
        'default_limits' => 'array',
        'screenshots' => 'array',
        'highlights' => 'array',
        'install_hooks' => 'array',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'requires_approval' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeForMarketplace($query)
    {
        return $query->active()->public();
    }

    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function scopePaid($query)
    {
        return $query->where('is_free', false);
    }

    // Relationships
    public function planModules(): HasMany
    {
        return $this->hasMany(PlanModule::class, 'module_key', 'key');
    }

    public function tenantSubscriptions(): HasMany
    {
        return $this->hasMany(TenantModuleSubscription::class, 'module_key', 'key');
    }

    public function changelogs(): HasMany
    {
        return $this->hasMany(ModuleChangelog::class, 'module_key', 'key');
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(ModuleDependency::class, 'module_key', 'key');
    }

    // Helpers
    public function getRouteKeyName(): string
    {
        return 'key';
    }

    public function hasDependency(string $moduleKey): bool
    {
        return in_array($moduleKey, $this->dependencies ?? []);
    }

    public function hasConflict(string $moduleKey): bool
    {
        return in_array($moduleKey, $this->conflicts ?? []);
    }

    public function getPrice(string $billingCycle = 'monthly'): ?float
    {
        return $billingCycle === 'yearly' ? $this->price_yearly : $this->price_monthly;
    }

    public function getYearlySavingsPercent(): ?int
    {
        if (!$this->price_monthly || !$this->price_yearly) {
            return null;
        }
        
        $monthlyCost = $this->price_monthly * 12;
        $savings = $monthlyCost - $this->price_yearly;
        
        return (int) round(($savings / $monthlyCost) * 100);
    }

    public function isCore(): bool
    {
        return $this->category === 'core';
    }

    public function requiresSetup(): bool
    {
        return !empty($this->migration_path) || !empty($this->seeder_class);
    }

    public function getInstallTimeEstimate(): string
    {
        $seconds = $this->estimated_install_time_seconds ?? 30;
        
        if ($seconds < 60) {
            return "{$seconds} seconds";
        }
        
        $minutes = ceil($seconds / 60);
        return "{$minutes} minute" . ($minutes > 1 ? 's' : '');
    }
}
