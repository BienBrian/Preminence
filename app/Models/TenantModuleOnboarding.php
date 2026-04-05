<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Tenant Module Onboarding Submission
 * 
 * Tracks the onboarding progress for a tenant requesting a module,
 * including form data, uploaded documents, and approval status.
 */
class TenantModuleOnboarding extends Model
{
    use HasFactory;

    protected $table = 'tenant_module_onboarding';

    protected $fillable = [
        'tenant_id',
        'module_key',
        'status',
        'form_data',
        'documents',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'rejection_reason',
        'review_notes',
        'network_participation_opt_in',
        'completed_at',
    ];

    protected $casts = [
        'form_data' => 'array',
        'documents' => 'array',
        'network_participation_opt_in' => 'boolean',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_NEEDS_INFO = 'needs_info';

    /**
     * Get the tenant this onboarding belongs to.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the module this onboarding is for.
     */
    public function module()
    {
        return $this->belongsTo(Module::class, 'module_key', 'key');
    }

    /**
     * Get the SuperAdmin who reviewed this submission.
     */
    public function reviewer()
    {
        return $this->belongsTo(SuperAdmin::class, 'reviewed_by');
    }

    /**
     * Check if submission is in draft status.
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if submission is pending review.
     */
    public function isPending(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_UNDER_REVIEW]);
    }

    /**
     * Check if submission was approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if submission was rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Mark as submitted.
     */
    public function submit(): void
    {
        $this->update([
            'status' => self::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
    }

    /**
     * Mark as approved.
     */
    public function approve(int $adminId, ?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'reviewed_at' => now(),
            'reviewed_by' => $adminId,
            'review_notes' => $notes,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark as rejected.
     */
    public function reject(int $adminId, string $reason, ?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'reviewed_at' => now(),
            'reviewed_by' => $adminId,
            'rejection_reason' => $reason,
            'review_notes' => $notes,
        ]);
    }

    /**
     * Mark as needing more info.
     */
    public function requestMoreInfo(int $adminId, string $message): void
    {
        $this->update([
            'status' => self::STATUS_NEEDS_INFO,
            'reviewed_at' => now(),
            'reviewed_by' => $adminId,
            'review_notes' => $message,
        ]);
    }

    /**
     * Get document upload URL or path.
     */
    public function getDocumentUrl(string $documentKey): ?string
    {
        $docs = $this->documents ?? [];
        return $docs[$documentKey] ?? null;
    }

    /**
     * Add or update a document.
     */
    public function setDocument(string $documentKey, string $path): void
    {
        $docs = $this->documents ?? [];
        $docs[$documentKey] = $path;
        $this->update(['documents' => $docs]);
    }

    /**
     * Scope: Pending review.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_UNDER_REVIEW]);
    }

    /**
     * Scope: For specific tenant.
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope: For specific module.
     */
    public function scopeForModule($query, string $moduleKey)
    {
        return $query->where('module_key', $moduleKey);
    }
}
