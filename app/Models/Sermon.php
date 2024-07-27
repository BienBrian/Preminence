<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sermon extends Model
{
    protected $table = "sermons";
    protected $fillable = [
        'title', 'description', 'banner', 'time', 'youtube', 'video', 'audio', 'sermondate', 'user_id', 'duration'
    ];
}
