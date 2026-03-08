<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class MpesaTransaction extends Model
{
    use BelongsToTenant;

    protected $table = 'mpesa_transactions';
    protected $fillable = [
        'tenant_id', 'TransactionType', 'TransID', 'TransTime', 'TransAmount',
        'BusinessShortCode', 'BillRefNumber', 'InvoiceNumber', 'OrgAccountBalance',
        'ThirdPartyTransID', 'MSISDN', 'FirstName', 'MiddleName', 'LastName',
    ];
}
