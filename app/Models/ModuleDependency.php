<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleDependency extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_key',
        'depends_on_key',
        'is_required',
        'min_version',
        'description',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    // Scopes
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeOptional($query)
    {
        return $query->where('is_required', false);
    }

    public function scopeForModule($query, string $moduleKey)
    {
        return $query->where('module_key', $moduleKey);
    }

    public function scopeDependentsOf($query, string $moduleKey)
    {
        return $query->where('depends_on_key', $moduleKey);
    }
}
