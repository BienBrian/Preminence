<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Funds extends Model
{
    use BelongsToTenant;

    protected $table = 'funds';
    protected $fillable = [
        'tenant_id', 'amount', 'description', 'source', 'user_id', 'mode',
    ];
}
