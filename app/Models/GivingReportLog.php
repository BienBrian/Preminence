<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Giving Report Log
 * 
 * Tracks all generated giving statements for audit and history.
 */
class GivingReportLog extends Model
{
    use HasFactory;

    protected $table = 'giving_report_logs';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'generated_by',
        'report_period_from',
        'report_period_to',
        'period_type',
        'categories_included',
        'total_amount',
        'transaction_count',
        'delivery_method',
        'email_sent_to',
        'email_verified_at',
        'sent_at',
        'opened_at',
        'password_protection_type',
        'password_hint',
        'file_path',
        'file_name',
        'file_format',
        'file_size_bytes',
        'status',
        'error_message',
        'generated_from_ip',
    ];

    protected $casts = [
        'categories_included' => 'array',
        'total_amount' => 'decimal:2',
        'report_period_from' => 'date',
        'report_period_to' => 'date',
        'email_verified_at' => 'datetime',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'file_size_bytes' => 'integer',
    ];

    /**
     * Get the recipient (member).
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias for recipient - the user who received the statement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the admin who generated the report.
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Alias for generator.
     */
    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Get the tenant.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the password record for this report.
     */
    public function password(): HasOne
    {
        return $this->hasOne(GivingStatementPassword::class, 'report_log_id');
    }

    /**
     * Scope: For a specific tenant.
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope: For a specific recipient.
     */
    public function scopeForRecipient($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: By status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Recent reports.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Sent via email.
     */
    public function scopeEmailed($query)
    {
        return $query->where('delivery_method', 'email');
    }

    /**
     * Check if report was emailed.
     */
    public function wasEmailed(): bool
    {
        return in_array($this->delivery_method, ['email', 'bulk']);
    }

    /**
     * Check if email was verified.
     */
    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Mark as sent.
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark as opened.
     */
    public function markAsOpened(): void
    {
        $this->update([
            'status' => 'opened',
            'opened_at' => now(),
        ]);
    }

    /**
     * Mark as failed.
     */
    public function markAsFailed(string $message): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $message,
        ]);
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSize(): ?string
    {
        if (!$this->file_size_bytes) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size_bytes;
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Get period label.
     */
    public function getPeriodLabel(): string
    {
        return $this->report_period_from->format('M Y') . ' - ' . $this->report_period_to->format('M Y');
    }
}
