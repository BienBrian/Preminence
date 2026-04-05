<?php

namespace App\Models\Discipleship;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MentorshipSession extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'mentorship_sessions';
    protected $fillable = [
        'tenant_id', 'mentorship_id', 'session_date', 'notes', 'created_by',
    ];

    protected $casts = [
        'session_date' => 'datetime',
    ];

    public function mentorship()
    {
        return $this->belongsTo(Mentorship::class, 'mentorship_id');
    }
}
