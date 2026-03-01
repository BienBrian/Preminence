<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

class OnboardingController extends Controller
{
    use \App\Traits\AutoHashesMpesaPhone;

    private function getPhoneCode()
    {
        return optional(DB::table('settings')->first())->phone_code ?? '254';
    }

    private function getSiteSettings()
    {
        return DB::table('settings')->first();
    }

    private function normalizePhone($input, $phoneCode)
    {
        $phone = preg_replace('/[^0-9]/', '', $input);
        if (strlen($phone) <= 10) {
            $phone = $phoneCode . ltrim($phone, '0');
        }
        return $phone;
    }

    private function generateOtp(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function sendSmsOtp(string $phone, string $otp, string $churchName, ?int $userId = null): void
    {
        $message = "Your {$churchName} verification code is: {$otp}. Valid for 15 minutes.";
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => env('SMS_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_POSTFIELDS     => 'partnerID=' . env('SMS_PARTNER_ID') .
                                      '&message=' . urlencode($message) .
                                      '&shortcode=' . env('SMS_SHORT_CODE') .
                                      '&mobile=' . $phone,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Bearer ' . env('SMS_API_KEY'),
            ],
        ]);
        curl_exec($curl);
        curl_close($curl);

        // Log the OTP SMS to sms + sms_recipients tables
        $mid = DB::table('sms')->insertGetId([
            'people_id' => 0,
            'message'   => $message,
            'category'  => 'verification',
            'sent'      => Carbon::now(),
        ]);
        DB::table('sms_recipients')->insert([
            'recipients' => $userId ?? 0,
            'phone'      => $phone,
            'sms_id'     => $mid,
            'sent'       => Carbon::now(),
        ]);
    }

    private function sendEmailOtp(string $email, string $otp, string $churchName): void
    {
        $message = "Your {$churchName} email verification code is: <strong>{$otp}</strong>.<br>This code is valid for 15 minutes.";
        $data = ['name' => 'New Member', 'mes' => $message];
        try {
            \Mail::send('dashboard.communication.mail', $data, function ($mail) use ($email, $churchName, $otp) {
                $mail->to($email)->subject("Email Verification Code — {$churchName}");
            });
        } catch (\Exception $e) {
            Log::error('OTP email failed: ' . $e->getMessage());
        }
    }

    public function show($token)
    {
        $invitation = Invitation::active()->where('token', $token)->first();
        $site_settings = $this->getSiteSettings();
        $phoneCode = $this->getPhoneCode();

        if (!$invitation) {
            $completed = Invitation::where('token', $token)->where('status', 'completed')->first();
            if ($completed) {
                return view('onboarding', [
                    'state' => 'completed',
                    'site_settings' => $site_settings,
                    'phoneCode' => $phoneCode,
                ]);
            }
            return view('onboarding', [
                'state' => 'expired',
                'site_settings' => $site_settings,
                'phoneCode' => $phoneCode,
            ]);
        }

        $step = $invitation->onboarding_step;

        // If step 0, mark as started and show step 1
        if ($step == 0) {
            $invitation->update([
                'status' => 'started',
                'started_at' => now(),
                'onboarding_step' => 1,
            ]);
            $step = 1;
        }

        if ($step >= 3 && $invitation->status === 'completed') {
            return view('onboarding', [
                'state' => 'completed',
                'site_settings' => $site_settings,
                'phoneCode' => $phoneCode,
            ]);
        }

        $user = $invitation->user_id ? User::find($invitation->user_id) : null;

        return view('onboarding', [
            'state'        => 'active',
            'step'         => $step,
            'invitation'   => $invitation,
            'user'         => $user,
            'site_settings'=> $site_settings,
            'phoneCode'    => $phoneCode,
            'token'        => $token,
        ]);
    }

    /**
     * Step 1: Collect phone, email, password → create user → send OTPs
     */
    public function step1(Request $request, $token)
    {
        $invitation = Invitation::active()->where('token', $token)->first();
        if (!$invitation) {
            return redirect()->route('onboarding', $token)->with('error', 'This invitation link has expired or is invalid.');
        }

        $phoneCode = $this->getPhoneCode();

        $validator = Validator::make($request->all(), [
            'phone'    => 'required|string',
            'email'    => 'nullable|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            // Flash step=1 so UI stays on step 1
            return redirect()->route('onboarding', $token)
                ->withErrors($validator)
                ->withInput()
                ->with('force_step', 1);
        }

        $phone = $this->normalizePhone($request->phone, $phoneCode);

        // Check if user with this phone already exists
        $user = User::where('phone', $phone)->first();
        if (!$user) {
            $user = new User();
            $user->firstname = '';
            $user->lastname  = '';
            $user->phone     = $phone;
            $user->status    = 1;
            $user->password  = Hash::make($request->password);
            $user->must_change_password = false;
            $user->save();

            $role = Role::where('name', 'Member')->first();
            if (!$role) $role = Role::where('name', 'User')->first();
            if ($role) $user->assignRole($role->name);
        } else {
            $user->password = Hash::make($request->password);
            $user->must_change_password = false;
            $user->save();
        }

        // Save email if provided
        if ($request->filled('email')) {
            $user->email = $request->email;
            $user->save();
        }

        // Generate OTPs
        $phoneOtp = $this->generateOtp();
        $emailOtp = $request->filled('email') ? $this->generateOtp() : null;
        $otpExpiry = Carbon::now()->addMinutes(15);

        $invitation->update([
            'user_id'        => $user->id,
            'phone'          => $phone,
            'email'          => $request->email,
            'phone_otp'      => $phoneOtp,
            'email_otp'      => $emailOtp,
            'otp_expires_at' => $otpExpiry,
            'onboarding_step'=> 2,
        ]);

        // Send OTPs
        $churchName = $this->getSiteSettings()->name ?? 'Church App';
        $this->sendSmsOtp($phone, $phoneOtp, $churchName, $user->id);
        if ($emailOtp && $request->filled('email')) {
            $this->sendEmailOtp($request->email, $emailOtp, $churchName);
        }

        // Auto-create mpesa hash
        $this->createMpesaHashAndMatch($phone, $user->firstname ?: 'New User', $user->id);

        return redirect()->route('onboarding', $token)
            ->with('success', 'Verification codes sent! Please check your phone' . ($request->filled('email') ? ' and email' : '') . '.');
    }

    /**
     * Step 2: Verify OTPs (or re-send if phone/email edited)
     */
    public function step2(Request $request, $token)
    {
        $invitation = Invitation::active()->where('token', $token)->first();
        if (!$invitation || !$invitation->user_id) {
            return redirect()->route('onboarding', $token)->with('error', 'Invalid session. Please start over.');
        }

        $user = User::findOrFail($invitation->user_id);
        $churchName = $this->getSiteSettings()->name ?? 'Church App';
        $phoneCode  = $this->getPhoneCode();

        // If user wants to edit phone/email and resend OTPs
        if ($request->has('resend') || $request->filled('new_phone') || $request->filled('new_email')) {
            $changed = false;

            if ($request->filled('new_phone')) {
                $newPhone = $this->normalizePhone($request->new_phone, $phoneCode);
                $user->phone = $newPhone;
                $user->save();
                $invitation->phone = $newPhone;
                $changed = true;
            }
            if ($request->filled('new_email')) {
                $user->email = $request->new_email;
                $user->save();
                $invitation->email = $request->new_email;
                $changed = true;
            }

            // Re-generate OTPs
            $phoneOtp = $this->generateOtp();
            $emailOtp = $user->email ? $this->generateOtp() : null;
            $invitation->phone_otp      = $phoneOtp;
            $invitation->email_otp      = $emailOtp;
            $invitation->otp_expires_at = Carbon::now()->addMinutes(15);
            $invitation->save();

            $this->sendSmsOtp($invitation->phone, $phoneOtp, $churchName, $user->id);
            if ($emailOtp && $user->email) {
                $this->sendEmailOtp($user->email, $emailOtp, $churchName);
            }

            return redirect()->route('onboarding', $token)
                ->with('success', 'New verification codes sent!');
        }

        // Validate OTP inputs
        $rules = ['phone_otp' => 'required|digits:6'];
        if ($invitation->email_otp) {
            $rules['email_otp'] = 'required|digits:6';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->route('onboarding', $token)
                ->withErrors($validator)
                ->withInput()
                ->with('force_step', 2);
        }

        // Check OTP expiry
        if ($invitation->otp_expires_at && Carbon::now()->isAfter($invitation->otp_expires_at)) {
            return redirect()->route('onboarding', $token)
                ->withErrors(['phone_otp' => 'Verification codes have expired. Please request new codes.'])
                ->with('force_step', 2);
        }

        // Verify phone OTP
        if ($request->phone_otp !== $invitation->phone_otp) {
            return redirect()->route('onboarding', $token)
                ->withErrors(['phone_otp' => 'Invalid phone verification code.'])
                ->withInput()
                ->with('force_step', 2);
        }

        // Verify email OTP
        if ($invitation->email_otp && $request->email_otp !== $invitation->email_otp) {
            return redirect()->route('onboarding', $token)
                ->withErrors(['email_otp' => 'Invalid email verification code.'])
                ->withInput()
                ->with('force_step', 2);
        }

        // Mark phone as verified
        $user->phone_verified_at = now();
        if ($invitation->email_otp) {
            $user->email_verified_at = now();
        }
        $user->save();

        $invitation->update([
            'phone_otp'       => null,
            'email_otp'       => null,
            'onboarding_step' => 3,
        ]);

        return redirect()->route('onboarding', $token)
            ->with('success', 'Phone' . ($user->email ? ' and email' : '') . ' verified successfully!');
    }

    /**
     * Step 3: Optional alternate phone numbers, then complete registration and log in
     */
    public function step3(Request $request, $token)
    {
        $invitation = Invitation::active()->where('token', $token)->first();
        if (!$invitation || !$invitation->user_id) {
            return redirect()->route('onboarding', $token)->with('error', 'Invalid session. Please start over.');
        }

        $user = User::findOrFail($invitation->user_id);
        $phoneCode = $this->getPhoneCode();

        // Save alternate phone(s) if provided
        if ($request->filled('alt_phone_1') || $request->filled('alt_phone_2')) {
            $altPhones = [];
            if ($request->filled('alt_phone_1')) {
                $altPhones[] = $this->normalizePhone($request->alt_phone_1, $phoneCode);
            }
            if ($request->filled('alt_phone_2')) {
                $altPhones[] = $this->normalizePhone($request->alt_phone_2, $phoneCode);
            }
            // Store in scontacts
            DB::table('scontacts')->updateOrInsert(
                ['user_id' => $user->id],
                ['secondary_phone' => implode(',', $altPhones)]
            );
        }

        // Complete invitation
        $invitation->update([
            'onboarding_step' => 3,
            'status'          => 'completed',
            'completed_at'    => now(),
        ]);

        // Log user in
        Auth::login($user);

        return redirect()->to('dashboard/home')
            ->with('success', 'Welcome! Your account has been created. You can complete your profile from here.');
    }

    /**
     * Resend OTPs (AJAX-friendly)
     */
    public function resendOtp(Request $request, $token)
    {
        $invitation = Invitation::active()->where('token', $token)->first();
        if (!$invitation || !$invitation->user_id) {
            return response()->json(['error' => 'Invalid invitation.'], 400);
        }

        // Rate limit resend — max once per 60 seconds
        if ($invitation->otp_expires_at && Carbon::now()->isBefore($invitation->otp_expires_at->subMinutes(14))) {
            return response()->json(['error' => 'Please wait at least 60 seconds before requesting a new code.'], 429);
        }

        $user = User::find($invitation->user_id);
        $churchName = $this->getSiteSettings()->name ?? 'Church App';

        $phoneOtp = $this->generateOtp();
        $emailOtp = $user && $user->email ? $this->generateOtp() : null;

        $invitation->update([
            'phone_otp'      => $phoneOtp,
            'email_otp'      => $emailOtp,
            'otp_expires_at' => Carbon::now()->addMinutes(15),
        ]);

        $this->sendSmsOtp($invitation->phone, $phoneOtp, $churchName, $user->id);
        if ($emailOtp && $user->email) {
            $this->sendEmailOtp($user->email, $emailOtp, $churchName);
        }

        return response()->json(['success' => 'New verification codes sent!']);
    }
}
