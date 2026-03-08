<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PledgeSms extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['tenant_id', 'pledge_id', 'message', 'status'];

    public function pledge()
    {
        return $this->belongsTo(Pledge::class);
    }
}
