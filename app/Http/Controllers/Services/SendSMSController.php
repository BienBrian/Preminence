<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Services\IntegrationService;
use Illuminate\Http\Request;

class SendSMSController extends Controller
{
    public function sendSMS($phone, $message)
    {
        return app(IntegrationService::class)->sendSms($phone, $message);
    }
}
