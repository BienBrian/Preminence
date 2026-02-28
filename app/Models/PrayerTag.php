<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrayerTag extends Model
{
    protected $fillable = ['name', 'color'];

    public function prayerRequests()
    {
        return $this->belongsToMany(PrayerRequest::class, 'prayer_request_tag');
    }
}
