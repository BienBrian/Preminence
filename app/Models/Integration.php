<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    protected $fillable = ['type', 'name', 'provider', 'config', 'is_active', 'is_default'];

    protected $casts = [
        'config' => 'encrypted:array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    protected static $sensitiveKeys = [
        'api_key', 'consumer_key', 'consumer_secret', 'passkey', 'password',
    ];

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
