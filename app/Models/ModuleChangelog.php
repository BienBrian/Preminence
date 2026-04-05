<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleChangelog extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_key',
        'version',
        'type',
        'changelog',
        'breaking_changes',
        'migration_required',
        'released_at',
    ];

    protected $casts = [
        'breaking_changes' => 'array',
        'migration_required' => 'array',
        'released_at' => 'datetime',
    ];

    // Scopes
    public function scopeForModule($query, string $moduleKey)
    {
        return $query->where('module_key', $moduleKey);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeBreaking($query)
    {
        return $query->whereNotNull('breaking_changes');
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('released_at', 'desc')->limit($limit);
    }

    // Helpers
    public function hasBreakingChanges(): bool
    {
        return !empty($this->breaking_changes);
    }

    public function requiresMigration(): bool
    {
        return !empty($this->migration_required);
    }
}
