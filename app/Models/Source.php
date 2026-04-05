<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Fund Source Model
 * 
 * Represents income/expense sources from the sources table.
 * ftype: 0 = Collections (Income), 1 = Expenditure (Expense)
 */
class Source extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'sources';
    protected $fillable = ['tenant_id', 'name', 'description', 'ftype'];

    /**
     * Scope to get only collection sources (ftype = 0).
     */
    public function scopeCollections($query)
    {
        return $query->where('ftype', 0);
    }

    /**
     * Scope to get only expense sources (ftype = 1).
     */
    public function scopeExpenses($query)
    {
        return $query->where('ftype', 1);
    }

    /**
     * Check if this is a collection source.
     */
    public function isCollection(): bool
    {
        return $this->ftype === 0;
    }

    /**
     * Check if this is an expense source.
     */
    public function isExpense(): bool
    {
        return $this->ftype === 1;
    }

    /**
     * Get summary categories linked to this source.
     */
    public function summaryCategories(): HasMany
    {
        return $this->hasMany(SummaryCategory::class, 'fund_source_id');
    }

    /**
     * Sync this source to a summary category.
     * Creates or updates the linked category.
     */
    public function syncToSummaryCategory(): SummaryCategory
    {
        return SummaryCategory::firstOrCreate(
            [
                'tenant_id' => $this->tenant_id,
                'fund_source_id' => $this->id,
            ],
            [
                'name' => $this->name,
                'code' => 'fund_' . $this->id,
                'description' => $this->description,
                'color' => '#007bff',
                'sort_order' => $this->id,
                'is_active' => true,
                'is_default' => true,
            ]
        );
    }
}
