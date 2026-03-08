<?php

namespace App\Models\Discipleship;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'recovery_journals';
    protected $fillable = ['tenant_id', 'user_id', 'title', 'content', 'mood', 'scripture'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
