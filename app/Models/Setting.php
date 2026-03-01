<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $fillable = [
        'name', 'slogan', 'address', 'theme', 'facebook', 'twitter', 'google', 'linkedin', 'youtube',
        'pinterest', 'instagram', 'whatsapp', 'icon', 'favicon', 'aboutus', 'contactus',
        'country_code', 'country_name', 'phone_code', 'county', 'city', 'specific_location',
        'recaptcha_enabled', 'recaptcha_site_key', 'recaptcha_secret_key',
    ];

    protected $casts = [
        'recaptcha_enabled' => 'boolean',
    ];
}
