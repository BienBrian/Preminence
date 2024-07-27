<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Funds extends Model
{
    protected $table = 'funds';
    protected $fillable = [
        'amount', 'description','source','user_id', 'mode',
    ];
}
