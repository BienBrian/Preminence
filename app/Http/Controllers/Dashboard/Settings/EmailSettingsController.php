<?php

namespace App\Http\Controllers\Dashboard\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class EmailSettingsController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Settings']);
    }

    public function index()
    {
        return view('dashboard.settings.email_settings');
    }
    public function addEmailSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mail_host' => 'required|string',
            'mail_username' => 'required|email',
            'mail_password' => 'required|string',
            'mail_from_address' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }
        $filePath = base_path('.env');
        $variables = [
            'MAIL_HOST' => $request->mail_host,
            'MAIL_USERNAME' => $request->mail_username,
            'MAIL_PASSWORD' => $request->mail_password,
            'MAIL_FROM_ADDRESS' => $request->mail_from_address,
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
            return response()->json(['success' => "Email Settings updated successfully!"]);
        } else {
            return response()->json(['error' => 'Unable to update Email Settings'], 401);
        }
    }
}
