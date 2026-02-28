<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrayerRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'description', 'category', 'request_type',
        'submitted_by', 'submitted_name', 'submitted_phone', 'submitted_alternate_phone', 'submitted_email',
        'visibility', 'is_anonymous', 'is_urgent', 'status',
        'assigned_to', 'prayer_count', 'moderation_status',
        'moderated_by', 'moderated_at', 'follow_up_at',
        'answered_at', 'closed_at', 'source', 'contact_preference',
    ];

    protected $casts = [
        'moderated_at' => 'datetime',
        'follow_up_at' => 'datetime',
        'answered_at'  => 'datetime',
        'closed_at'    => 'datetime',
    ];

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function notes()
    {
        return $this->hasMany(PrayerRequestNote::class);
    }

    public function tags()
    {
        return $this->belongsToMany(PrayerTag::class, 'prayer_request_tag');
    }

    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', 1);
    }

    public function scopePublicApproved($query)
    {
        return $query->where('visibility', 'public')->where('moderation_status', 'approved');
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeNeedingFollowUp($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($sq) {
                $sq->whereNotNull('follow_up_at')->where('follow_up_at', '<=', now());
            })->orWhere(function ($sq) {
                $sq->whereNull('follow_up_at')
                   ->where('status', 'new')
                   ->where('created_at', '<=', now()->subDays(7));
            });
        })->whereIn('status', ['new', 'in_progress', 'prayed_for']);
    }
}
