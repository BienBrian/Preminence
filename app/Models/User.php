<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\BelongsToTenant;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id', 'firstname', 'surname', 'lastname', 'email', 'password', 'phone', 'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'verification_token_expires_at' => 'datetime',
    ];

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Get the tenant this user belongs to.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Scope: Active users only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Inactive users.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', '!=', 'active');
    }

    /**
     * Get the alternative phones for this user.
     */
    public function alternativePhones()
    {
        return $this->hasMany(AlternativePhone::class);
    }

    /**
     * Get all phone hashes (primary + alternatives) for MPESA matching.
     */
    public function getAllPhoneHashes(): array
    {
        $hashes = [];
        
        // Primary phone hash
        if ($this->phone) {
            $hashes[] = AlternativePhone::generateHash($this->phone);
        }
        
        // Alternative phone hashes
        foreach ($this->alternativePhones as $altPhone) {
            $hashes[] = $altPhone->phone_hash;
        }
        
        return array_unique($hashes);
    }

    /**
     * Check if user has a phone number (primary or alternative).
     */
    public function hasPhone(): bool
    {
        return !empty($this->phone) || $this->alternativePhones()->exists();
    }

    /**
     * Get all phone numbers (primary + alternatives) as array.
     */
    public function getAllPhones(): array
    {
        $phones = [];
        
        if ($this->phone) {
            $phones[] = [
                'phone' => $this->phone,
                'type' => 'primary',
                'verified' => !is_null($this->phone_verified_at),
            ];
        }
        
        foreach ($this->alternativePhones as $alt) {
            $phones[] = [
                'phone' => $alt->phone,
                'type' => $alt->label ?? 'alternative',
                'verified' => $alt->is_verified,
            ];
        }
        
        return $phones;
    }
}
