<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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

    public function show($token)
    {
        $invitation = Invitation::active()->where('token', $token)->first();
        $site_settings = $this->getSiteSettings();
        $phoneCode = $this->getPhoneCode();

        if (!$invitation) {
            // Check if it's completed
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

        // Determine step
        $step = $invitation->onboarding_step;

        // If step 0, mark as started
        if ($step == 0) {
            $invitation->update([
                'status' => 'started',
                'started_at' => now(),
                'onboarding_step' => 1,
            ]);
            $step = 1;
        }

        // If step is already 3, show completed
        if ($step >= 3 && $invitation->status === 'completed') {
            return view('onboarding', [
                'state' => 'completed',
                'site_settings' => $site_settings,
                'phoneCode' => $phoneCode,
            ]);
        }

        // Load user if linked
        $user = $invitation->user_id ? User::find($invitation->user_id) : null;

        return view('onboarding', [
            'state' => 'active',
            'step' => $step,
            'invitation' => $invitation,
            'user' => $user,
            'site_settings' => $site_settings,
            'phoneCode' => $phoneCode,
            'token' => $token,
        ]);
    }

    public function step1(Request $request, $token)
    {
        $invitation = Invitation::active()->where('token', $token)->first();
        if (!$invitation) {
            return redirect()->route('onboarding', $token)->with('error', 'This invitation link has expired or is invalid.');
        }

        $phoneCode = $this->getPhoneCode();

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->route('onboarding', $token)
                ->withErrors($validator)
                ->withInput();
        }

        $phone = $this->normalizePhone($request->phone, $phoneCode);

        // Check if user with this phone already exists
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            // Create new user
            $user = new User();
            $user->firstname = '';
            $user->lastname = '';
            $user->phone = $phone;
            $user->status = 1;
            $user->password = Hash::make($request->password);
            $user->must_change_password = false;
            $user->phone_verified_at = now();
            $user->save();

            // Assign Member role
            $role = Role::where('name', 'Member')->first();
            if (!$role) $role = Role::where('name', 'User')->first();
            if ($role) $user->assignRole($role->name);
        } else {
            // Update existing user's password
            $user->password = Hash::make($request->password);
            $user->must_change_password = false;
            $user->phone_verified_at = $user->phone_verified_at ?? now();
            $user->save();
        }

        // Auto-create mpesa hash and retro-match funds
        $this->createMpesaHashAndMatch($phone, $user->firstname ?: 'New User', $user->id);

        $invitation->update([
            'user_id' => $user->id,
            'onboarding_step' => 2,
        ]);

        return redirect()->route('onboarding', $token);
    }

    public function step2(Request $request, $token)
    {
        $invitation = Invitation::active()->where('token', $token)->first();
        if (!$invitation || !$invitation->user_id) {
            return redirect()->route('onboarding', $token)->with('error', 'Invalid session. Please start over.');
        }

        $user = User::findOrFail($invitation->user_id);

        if ($request->filled('email')) {
            $validator = Validator::make($request->all(), [
                'email' => 'email|max:255|unique:users,email,' . $user->id,
            ]);

            if ($validator->fails()) {
                return redirect()->route('onboarding', $token)
                    ->withErrors($validator)
                    ->withInput();
            }

            $user->email = $request->email;
            $user->email_verified_at = now();
            $user->save();
        }

        $invitation->update([
            'onboarding_step' => 3,
        ]);

        return redirect()->route('onboarding', $token);
    }

    public function step3(Request $request, $token)
    {
        $invitation = Invitation::active()->where('token', $token)->first();
        if (!$invitation || !$invitation->user_id) {
            return redirect()->route('onboarding', $token)->with('error', 'Invalid session. Please start over.');
        }

        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'gender' => 'nullable|integer',
            'dob' => 'nullable|date',
            'marital' => 'nullable|integer',
            'resident' => 'nullable|string|max:255',
            'children' => 'nullable|array|max:10',
            'children.*.firstname' => 'nullable|string|max:255',
            'children.*.lastname' => 'nullable|string|max:255',
            'children.*.gender' => 'nullable|string|max:10',
            'children.*.dob' => 'nullable|date',
            'spouse_name' => 'nullable|string|max:255',
            'spouse_phone' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->route('onboarding', $token)
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::findOrFail($invitation->user_id);

        // Update user basic info
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->surname = $request->surname;
        $user->save();

        // Save contacts (gender, dob, marital)
        // contacts table has NOT NULL columns (phone, city, country, gender, dob) - provide defaults
        $contactData = [
            'gender' => $request->gender ?? '',
            'dob' => $request->dob ?? '',
            'marital' => $request->marital,
        ];
        $existingContact = DB::table('contacts')->where('user_id', $user->id)->first();
        if ($existingContact) {
            DB::table('contacts')->where('user_id', $user->id)->update($contactData);
        } else {
            $contactData['user_id'] = $user->id;
            $contactData['phone'] = $user->phone ? '0' . substr($user->phone, strlen($this->getPhoneCode())) : '';
            $contactData['city'] = '';
            $contactData['country'] = '';
            DB::table('contacts')->insert($contactData);
        }

        // Save secondary contacts (resident)
        if ($request->filled('resident')) {
            DB::table('scontacts')->updateOrInsert(
                ['user_id' => $user->id],
                ['resident' => $request->resident]
            );
        }

        // Save children
        if ($request->filled('children')) {
            foreach ($request->children as $child) {
                if (empty($child['firstname']) && empty($child['lastname'])) continue;

                $childId = DB::table('children')->insertGetId([
                    'firstname' => $child['firstname'] ?? '',
                    'lastname' => $child['lastname'] ?? '',
                    'gender' => $child['gender'] ?? null,
                    'dob' => !empty($child['dob']) ? $child['dob'] : null,
                    'user_id' => 0,
                    'created_at' => now(),
                ]);

                if ($childId) {
                    DB::table('guardians')->insert([
                        'child_id' => $childId,
                        'user_id' => $user->id,
                        'relationship' => 'Parent',
                        'created_at' => now(),
                    ]);
                }
            }
        }

        // Save spouse/family
        if ($request->filled('spouse_name') || $request->filled('spouse_phone')) {
            $familyData = [];
            if ($request->filled('spouse_name')) $familyData['name'] = $request->spouse_name;
            if ($request->filled('spouse_phone')) {
                $phoneCode = $this->getPhoneCode();
                $familyData['phone'] = $this->normalizePhone($request->spouse_phone, $phoneCode);
            }
            $familyData['relationship'] = 1; // Spouse
            DB::table('families')->updateOrInsert(['user_id' => $user->id], $familyData);
        }

        // Complete invitation
        $invitation->update([
            'onboarding_step' => 3,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Log user in
        Auth::login($user);

        return redirect()->to('dashboard/home')->with('success', 'Welcome! Your profile has been completed successfully.');
    }
}
