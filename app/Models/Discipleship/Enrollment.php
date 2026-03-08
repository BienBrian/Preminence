<?php

namespace App\Models\Discipleship;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'discipleship_enrollments';
    protected $fillable = ['tenant_id', 'track_id', 'user_id', 'status', 'started_at', 'completed_at'];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function track()
    {
        return $this->belongsTo(Track::class, 'track_id');
    }

    public function progress()
    {
        return $this->hasMany(Progress::class, 'enrollment_id');
    }
}
