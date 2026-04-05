<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SummaryCategory extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'summary_categories';
    protected $fillable = [
        'tenant_id',
        'fund_source_id',
        'name',
        'code',
        'description',
        'color',
        'sort_order',
        'is_active',
        'is_default'
    ];

    /**
     * Get all reference mappings for this category.
     */
    public function referenceMappings(): HasMany
    {
        return $this->hasMany(ReferenceTypeMapping::class, 'summary_category_id');
    }

    /**
     * Get the linked fund source.
     */
    public function fundSource(): BelongsTo
    {
        return $this->belongsTo(Source::class, 'fund_source_id');
    }

    /**
     * Scope to get only collection categories (linked to ftype=0 sources).
     * For MPESA logs, we only want collection sources (income), not expenses.
     */
    public function scopeCollections($query)
    {
        return $query->where(function ($q) {
            $q->whereHas('fundSource', function ($sq) {
                $sq->where('ftype', 0); // Collection sources only
            })->orWhereNull('fund_source_id'); // Or custom categories
        });
    }

    /**
     * Get all reference types (original_refs) associated with this category.
     */
    public function getReferenceTypesAttribute(): array
    {
        return $this->referenceMappings()
            ->where('is_active', true)
            ->pluck('original_ref')
            ->toArray();
    }

    /**
     * Scope to get only active categories ordered by sort_order.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    /**
     * Get category by code.
     */
    public static function findByCode(string $code): ?self
    {
        return self::where('code', $code)->where('is_active', true)->first();
    }

    /**
     * Get all reference types grouped by category.
     */
    public static function getGroupedReferences(): array
    {
        $categories = self::active()->with('referenceMappings')->get();
        $grouped = [];

        foreach ($categories as $category) {
            $refs = $category->referenceMappings
                ->where('is_active', true)
                ->pluck('original_ref')
                ->toArray();
            
            if (!empty($refs)) {
                $grouped[$category->code] = [
                    'name' => $category->name,
                    'color' => $category->color,
                    'references' => $refs,
                ];
            }
        }

        return $grouped;
    }
}
