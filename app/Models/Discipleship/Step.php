<?php

namespace App\Models\Discipleship;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Step extends Model
{
    protected $table = 'discipleship_steps';
    protected $fillable = ['track_id', 'title', 'description', 'order', 'type'];

    public function track()
    {
        return $this->belongsTo(Track::class, 'track_id');
    }
}
