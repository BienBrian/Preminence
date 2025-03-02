<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PledgeSms extends Model
{
    use HasFactory;
    protected $fillable = ["pledge_id", "message", "status"];

    public function pledge(){
        return $this->belongsTo(Pledge::class);
    }
}
