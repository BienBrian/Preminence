<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

/**
 * Giving Statement Password
 * 
 * Manages password protection for individual giving statements.
 */
class GivingStatementPassword extends Model
{
    use HasFactory;

    protected $table = 'giving_statement_passwords';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'report_log_id',
        'password_type',
        'password_hash',
        'sms_otp_code',
        'sms_otp_expires_at',
        'sms_sent_to',
        'sms_sent_at',
        'used_at',
        'access_count',
        'last_accessed_at',
        'last_accessed_from_ip',
        'is_revoked',
        'revoked_at',
        'revoked_by',
    ];

    protected $casts = [
        'sms_otp_expires_at' => 'datetime',
        'sms_sent_at' => 'datetime',
        'used_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'revoked_at' => 'datetime',
        'is_revoked' => 'boolean',
        'access_count' => 'integer',
    ];

    /**
     * Get the report log this password belongs to.
     */
    public function reportLog(): BelongsTo
    {
        return $this->belongsTo(GivingReportLog::class, 'report_log_id');
    }

    /**
     * Get the user (recipient).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tenant.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the admin who revoked this password.
     */
    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /**
     * Set the password (hashes it).
     */
    public function setPassword(string $password): void
    {
        $this->password_hash = Hash::make($password);
    }

    /**
     * Verify a password.
     */
    public function verifyPassword(string $password): bool
    {
        if (!$this->password_hash) {
            return false;
        }
        return Hash::check($password, $this->password_hash);
    }

    /**
     * Check if SMS OTP is valid and not expired.
     */
    public function isValidOtp(string $code): bool
    {
        if ($this->password_type !== 'sms_otp') {
            return false;
        }

        if ($this->is_revoked) {
            return false;
        }

        if ($this->sms_otp_expires_at && $this->sms_otp_expires_at->isPast()) {
            return false;
        }

        return $this->sms_otp_code === $code;
    }

    /**
     * Generate a new SMS OTP.
     */
    public function generateOtp(int $length = 6): string
    {
        $code = str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
        
        $this->sms_otp_code = $code;
        $this->sms_otp_expires_at = now()->addHours(24);
        $this->save();

        return $code;
    }

    /**
     * Mark as used.
     */
    public function markAsUsed(?string $ip = null): void
    {
        $this->access_count++;
        $this->last_accessed_at = now();
        $this->last_accessed_from_ip = $ip;
        
        if (!$this->used_at) {
            $this->used_at = now();
        }
        
        $this->save();
    }

    /**
     * Revoke this password.
     */
    public function revoke(int $adminId): void
    {
        $this->update([
            'is_revoked' => true,
            'revoked_at' => now(),
            'revoked_by' => $adminId,
        ]);
    }

    /**
     * Check if password is revoked.
     */
    public function isRevoked(): bool
    {
        return $this->is_revoked;
    }

    /**
     * Check if this is an OTP type.
     */
    public function isOtp(): bool
    {
        return $this->password_type === 'sms_otp';
    }

    /**
     * Get password hint based on type.
     */
    public function getPasswordHint(): string
    {
        return match ($this->password_type) {
            'phone' => 'Last 4 digits of your phone number',
            'id_number' => 'Last 4 digits of your National ID',
            'custom_code' => 'The code provided by your church',
            'sms_otp' => 'The OTP sent to your phone',
            default => 'See email for password details',
        };
    }

    /**
     * Scope: Valid (not revoked, not expired for OTP).
     */
    public function scopeValid($query)
    {
        return $query->where('is_revoked', false)
            ->where(function ($q) {
                $q->whereNull('sms_otp_expires_at')
                  ->orWhere('sms_otp_expires_at', '>', now());
            });
    }

    /**
     * Scope: By OTP code.
     */
    public function scopeByOtp($query, string $code)
    {
        return $query->where('sms_otp_code', $code);
    }

    /**
     * Clean up expired OTPs (for scheduled job).
     */
    public static function cleanupExpiredOtps(): int
    {
        return self::where('password_type', 'sms_otp')
            ->where('sms_otp_expires_at', '<', now())
            ->where('used_at', null)
            ->delete();
    }
}
