<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifyProfileController extends Controller
{
    public function show($token)
    {
        $user = User::where('verification_token', $token)
            ->where('verification_token_expires_at', '>', now())
            ->first();

        if (!$user) {
            return view('verify-profile', ['expired' => true]);
        }

        $site_settings = DB::table('settings')->first();
        $scontact = DB::table('scontacts')->where('user_id', $user->id)->first();

        return view('verify-profile', [
            'expired' => false,
            'user' => $user,
            'scontact' => $scontact,
            'site_settings' => $site_settings,
            'token' => $token,
        ]);
    }

    public function submit(Request $request, $token)
    {
        $user = User::where('verification_token', $token)
            ->where('verification_token_expires_at', '>', now())
            ->first();

        if (!$user) {
            return redirect()->back()->with('error', 'This verification link has expired.');
        }

        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'phone' => 'required|string',
            'email' => 'nullable|email|max:255',
        ]);

        // Update user basic info
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;

        $phoneCode = optional(DB::table('settings')->first())->phone_code ?? '254';
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        if (strlen($phone) <= 10) {
            $phone = $phoneCode . ltrim($phone, '0');
        }
        $user->phone = $phone;

        if ($request->filled('email')) {
            $user->email = $request->email;
        }

        // Mark as verified
        $user->email_verified_at = $user->email ? now() : $user->email_verified_at;
        $user->phone_verified_at = now();
        $user->verification_token = null;
        $user->verification_token_expires_at = null;
        $user->save();

        // Update contacts
        $contactData = [];
        if ($request->filled('dob')) {
            $contactData['dob'] = $request->dob;
        }
        if ($request->filled('gender')) {
            $contactData['gender'] = $request->gender;
        }
        if (!empty($contactData)) {
            DB::table('contacts')->updateOrInsert(
                ['user_id' => $user->id],
                $contactData
            );
        }

        // Update secondary contacts
        $scontactData = [];
        if ($request->filled('resident')) {
            $scontactData['resident'] = $request->resident;
        }
        if ($request->filled('town')) {
            $scontactData['town'] = $request->town;
        }
        if (!empty($scontactData)) {
            DB::table('scontacts')->updateOrInsert(
                ['user_id' => $user->id],
                $scontactData
            );
        }

        $site_settings = DB::table('settings')->first();
        return view('verify-profile', [
            'expired' => false,
            'completed' => true,
            'user' => $user,
            'site_settings' => $site_settings,
        ]);
    }
}
