<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use BelongsToTenant;

    protected $table = 'testimonials';
    protected $fillable = ['tenant_id', 'testimonial', 'user_id', 'status'];
}
