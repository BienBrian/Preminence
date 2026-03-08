<?php

namespace App\Models\Discipleship;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Step extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'discipleship_steps';
    protected $fillable = ['tenant_id', 'track_id', 'title', 'description', 'order', 'type'];

    public function track()
    {
        return $this->belongsTo(Track::class, 'track_id');
    }
}
