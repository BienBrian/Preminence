<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'token', 'phone', 'email', 'via', 'invited_by', 'user_id',
        'status', 'onboarding_step', 'expires_at', 'started_at', 'completed_at',
        'phone_otp', 'email_otp', 'otp_expires_at',
    ];

    protected $casts = [
        'expires_at'     => 'datetime',
        'started_at'     => 'datetime',
        'completed_at'   => 'datetime',
        'otp_expires_at' => 'datetime',
    ];

    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'started'])
            ->where('expires_at', '>', now());
    }
}
