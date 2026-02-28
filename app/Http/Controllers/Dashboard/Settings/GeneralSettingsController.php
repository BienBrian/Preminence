<?php

namespace App\Http\Controllers\Dashboard\Settings;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GeneralSettingsController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Website Settings']);
    }

    public function index()
    {
        $settings = Setting::first();
        return view('dashboard.settings.general_settings', compact('settings'));
    }

    public function addGeneralSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'slogan' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'theme' => 'nullable|string|max:50',
            'country_code' => 'nullable|string|max:10',
            'country_name' => 'nullable|string|max:100',
            'phone_code' => 'nullable|string|max:10',
            'county' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'specific_location' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'google' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'pinterest' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'aboutus' => 'nullable|string',
            'contactus' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        $settings = Setting::first();
        if ($settings == null) {
            $settings = new Setting;
            $settings->icon = '';
            $settings->favicon = '';
        }

        $settings->name = $request->name ?? '';
        $settings->slogan = $request->slogan ?? '';
        $settings->address = $request->address ?? '';
        $settings->theme = $request->theme ?? 'blue.css';
        $settings->country_code = $request->country_code;
        $settings->country_name = $request->country_name;
        $settings->phone_code = $request->phone_code;
        $settings->county = $request->county;
        $settings->city = $request->city;
        $settings->specific_location = $request->specific_location;
        $settings->facebook = $request->facebook ?? '';
        $settings->twitter = $request->twitter ?? '';
        $settings->google = $request->google ?? '';
        $settings->linkedin = $request->linkedin ?? '';
        $settings->youtube = $request->youtube ?? '';
        $settings->pinterest = $request->pinterest ?? '';
        $settings->instagram = $request->instagram ?? '';
        $settings->whatsapp = $request->whatsapp ?? '';
        $settings->aboutus = $this->purify($request->aboutus);
        $settings->contactus = $this->purify($request->contactus);

        if ($settings->save()) {
            return response()->json(['success' => 'Settings updated successfully']);
        } else {
            return response()->json(['error' => 'Unable to update settings'], 401);
        }
    }
}
