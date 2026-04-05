<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlternativePhone extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'user_id',
        'phone',
        'phone_hash',
        'label',
        'is_verified',
        'verified_at',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the user that owns this alternative phone.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate hash for the phone number.
     */
    public static function generateHash(string $phone): string
    {
        // Normalize phone to international format
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
            $phone = '254' . substr($phone, 1);
        } elseif (strlen($phone) == 9) {
            $phone = '254' . $phone;
        } elseif (substr($phone, 0, 1) == '+') {
            $phone = substr($phone, 1);
        }
        
        return hash('sha256', $phone);
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->phone_hash) && !empty($model->phone)) {
                $model->phone_hash = self::generateHash($model->phone);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('phone')) {
                $model->phone_hash = self::generateHash($model->phone);
                $model->is_verified = false;
                $model->verified_at = null;
            }
        });
    }
}
