<?php

namespace App\Models\Discipleship;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mentorship extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'mentorships';
    protected $fillable = ['tenant_id', 'mentor_id', 'mentee_id', 'status', 'started_at', 'ended_at', 'notes'];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    public function mentor()
    {
        return $this->belongsTo(\App\Models\User::class, 'mentor_id');
    }

    public function mentee()
    {
        return $this->belongsTo(\App\Models\User::class, 'mentee_id');
    }

    public function sessions()
    {
        return $this->hasMany(MentorshipSession::class, 'mentorship_id');
    }
}
