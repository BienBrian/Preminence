<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Prayer extends Model
{
    use BelongsToTenant;

    protected $table = 'prayers';
    protected $fillable = ['tenant_id', 'title', 'description', 'user_id', 'status'];
}
