<?php

namespace App\Models\Discipleship;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'discipleship_tracks';
    protected $fillable = ['tenant_id', 'name', 'description', 'created_by', 'is_active'];

    public function steps()
    {
        return $this->hasMany(Step::class, 'track_id')->orderBy('order');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'track_id');
    }
}
