<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class MissingMpesaPhone extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'trans_id', 'phone_hash', 'phone', 'trans_date'];
}
