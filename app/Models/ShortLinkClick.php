<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortLinkClick extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'short_link_id',
        'ip_address',
        'user_agent',
        'referer',
        'country',
        'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function shortLink()
    {
        return $this->belongsTo(ShortLink::class);
    }
}
