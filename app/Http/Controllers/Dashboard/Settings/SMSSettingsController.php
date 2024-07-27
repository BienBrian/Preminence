<?php

namespace App\Http\Controllers\Dashboard\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class SMSSettingsController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Settings']);
    }

    public function index()
    {
        return view('dashboard.settings.sms_settings');
    }
    public function addSmsSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sms_url' => 'nullable|url',
            'sms_api_key' => 'nullable|string',
            'sms_short_code' => 'nullable|string',
            'partner_id' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }
        $filePath = base_path('.env');
        $variables = [
            'SMS_URL' => $request->sms_url,
            'SMS_API_KEY' => $request->sms_api_key,
            'SMS_SHORT_CODE' => $request->sms_short_code,
            'SMS_PARTNER_ID' => $request->sms_partner_id,
        ];

        if (!File::exists($filePath)) {
            return response()->json(['error' => '.env file does not exist.'], 401);
        }

        $envContent = File::get($filePath);
        $envLines = explode("\n", $envContent);

        $updatedContent = '';
        $existingVars = [];
        foreach ($envLines as $line) {
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                if (array_key_exists($key, $variables)) {
                    $value = $variables[$key];
                }

                $existingVars[$key] = $value;
                $updatedContent .= "{$key}={$value}\n";
            }else {
                $updatedContent .= "{$line}\n";
            }
        }

        foreach ($variables as $key => $value) {
            if (!array_key_exists($key, $existingVars)) {
                $updatedContent .= "{$key}={$value}\n";
            }
        }

        if (File::put($filePath, trim($updatedContent))) {
            return response()->json(['success' => "SMS Settings updated successfully!"]);
        } else {
            return response()->json(['error' => 'Unable to update SMS Settings'], 401);
        }
    }
}
