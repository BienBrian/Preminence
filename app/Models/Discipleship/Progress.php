<?php

namespace App\Models\Discipleship;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    protected $table = 'discipleship_progress';
    protected $fillable = ['enrollment_id', 'step_id', 'completed_at', 'notes'];
    protected $dates = ['completed_at'];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function step()
    {
        return $this->belongsTo(Step::class, 'step_id');
    }
}
