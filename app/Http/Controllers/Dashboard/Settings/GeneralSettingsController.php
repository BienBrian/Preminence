<?php

namespace App\Http\Controllers\Dashboard\Settings;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\MaturitySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GeneralSettingsController extends Controller
{
    public function __construct(){
        $this->middleware(['auth', 'verified']);
        $this->middleware(['permission:View General Settings']);
    }

    public function index(){
        $generalSettings = GeneralSetting::first();
        return view('dashboard.settings.general_settings', @compact('generalSettings'));
    }

    public function addGeneralSettings(Request $request){
        $validator = Validator::make($request->all(), [
            'show_auction_shares' => 'nullable|integer|min:0|max:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        $generalSettings = GeneralSetting::first();
        if ($generalSettings == null) {
            $generalSettings = new GeneralSetting;
        }
        $generalSettings->show_auction_shares = $request->has('show_auction_shares');
        if ($generalSettings->save()) {
            return response()->json(['success' => "General Settings updated successfully"]);
        } else {
            return response()->json(['error' => 'Unable to update general settings'], 401);
        }
    }
}
