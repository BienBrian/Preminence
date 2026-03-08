<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use App\Traits\BelongsToTenant;

class Integration extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'type', 'name', 'provider', 'config', 'is_active', 'is_default'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    protected static $sensitiveKeys = [
        'api_key', 'consumer_key', 'consumer_secret', 'passkey', 'password',
    ];

    /**
     * Get config attribute - handles both encrypted and plain JSON
     */
    public function getConfigAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        // Try to decrypt first (for encrypted values)
        try {
            $decrypted = decrypt($value);
            return is_array($decrypted) ? $decrypted : json_decode($decrypted, true) ?? [];
        } catch (\Exception $e) {
            // If decryption fails, try as plain JSON
            $json = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        return [];
    }

    /**
     * Set config attribute - encrypts the value
     */
    public function setConfigAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['config'] = encrypt(json_encode($value));
        } else {
            $this->attributes['config'] = encrypt($value);
        }
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function getConfigValue($key)
    {
        return $this->config[$key] ?? null;
    }

    public function getMaskedConfig()
    {
        $config = $this->config ?? [];
        $masked = [];
        foreach ($config as $key => $value) {
            if (in_array($key, static::$sensitiveKeys) && !empty($value)) {
                $masked[$key] = str_repeat('*', max(0, strlen($value) - 4)) . substr($value, -4);
            } else {
                $masked[$key] = $value;
            }
        }
        return $masked;
    }
}
