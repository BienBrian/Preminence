<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModulePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_key',
        'permission_key',
        'name',
        'description',
        'level',
        'is_premium',
        'requires_features',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'requires_features' => 'array',
        'is_premium' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForModule($query, string $moduleKey)
    {
        return $query->where('module_key', $moduleKey);
    }

    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    public function scopeBasic($query)
    {
        return $query->where('level', 'basic');
    }

    public function scopeAdvanced($query)
    {
        return $query->where('level', 'advanced');
    }

    // Relationships
    public function grants(): HasMany
    {
        return $this->hasMany(TenantModulePermissionGrant::class);
    }
}
