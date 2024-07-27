<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $fillable = [
        'image', 'description', 'category', 'date_uploaded'
    ];
}
