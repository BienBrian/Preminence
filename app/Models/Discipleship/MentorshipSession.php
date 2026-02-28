<?php

namespace App\Models\Discipleship;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MentorshipSession extends Model
{
    protected $table = 'mentorship_sessions';
    protected $fillable = ['mentorship_id', 'session_date', 'notes', 'duration_minutes', 'created_by'];
    protected $casts = [
        'session_date' => 'datetime',
    ];

    public function mentorship()
    {
        return $this->belongsTo(Mentorship::class, 'mentorship_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
