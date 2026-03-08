<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PrayerTag extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'color'];

    public function prayerRequests()
    {
        return $this->belongsToMany(PrayerRequest::class, 'prayer_request_tag');
    }
}
