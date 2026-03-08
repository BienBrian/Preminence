<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PrayerRequestNote extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'prayer_request_id', 'user_id', 'note', 'type',
    ];

    public function prayerRequest()
    {
        return $this->belongsTo(PrayerRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
