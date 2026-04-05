<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferenceTypeMapping extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'reference_type_mappings';
    protected $fillable = ['tenant_id', 'original_ref', 'mapped_ref', 'summary_category_id', 'description', 'is_active', 'auto_discovered'];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_discovered' => 'boolean',
    ];

    /**
     * Get the summary category for this mapping.
     */
    public function summaryCategory(): BelongsTo
    {
        return $this->belongsTo(SummaryCategory::class, 'summary_category_id');
    }

    /**
     * Get a mapped reference type, returning the normalized version if found.
     *
     * @param string $originalRef
     * @return string
     */
    public static function getMappedRef(string $originalRef): string
    {
        $mapping = self::where('original_ref', strtolower(trim($originalRef)))
            ->where('is_active', true)
            ->first();

        return $mapping ? $mapping->mapped_ref : $originalRef;
    }

    /**
     * Get the category for a reference type.
     *
     * @param string $originalRef
     * @return SummaryCategory|null
     */
    public static function getCategory(string $originalRef): ?SummaryCategory
    {
        $mapping = self::where('original_ref', strtolower(trim($originalRef)))
            ->where('is_active', true)
            ->whereNotNull('summary_category_id')
            ->with('summaryCategory')
            ->first();

        return $mapping ? $mapping->summaryCategory : null;
    }

    /**
     * Get all active mappings as an associative array [original => mapped].
     *
     * @return array
     */
    public static function getAllMappings(): array
    {
        return self::where('is_active', true)
            ->pluck('mapped_ref', 'original_ref')
            ->toArray();
    }
}
