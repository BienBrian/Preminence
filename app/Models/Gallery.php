<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use BelongsToTenant;

    protected $table = 'gallery';
    protected $fillable = ['tenant_id', 'image', 'description', 'category', 'date_uploaded'];
}
