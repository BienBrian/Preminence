<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Sermon extends Model
{
    use BelongsToTenant;

    protected $table = 'sermons';
    protected $fillable = [
        'tenant_id', 'title', 'description', 'banner', 'time',
        'youtube', 'video', 'audio', 'sermondate', 'user_id', 'duration',
    ];
}
