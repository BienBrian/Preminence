<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissingMpesaPhone extends Model
{
    protected $fillable = ['name','trans_id','phone_hash','phone', 'trans_date'];
}
