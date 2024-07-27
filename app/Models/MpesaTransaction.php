<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpesaTransaction extends Model
{
    protected $table = "mpesa_transactions";
    protected $fillable = ['TransactionType', 'TransID', 'TransTime', 'TransAmount', 'BusinessShortCode', 'BillRefNumber', 'InvoiceNumber', 'OrgAccountBalance',
    'ThirdPartyTransID', 'MSISDN', 'FirstName', 'MiddleName', 'LastName',];
}
