<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Rules\Recaptcha;
use App\Traits\AutoHashesMpesaPhone;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    use RegistersUsers, AutoHashesMpesaPhone;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');

        $site_settings = DB::table('settings')->first();
        \View::share('site_settings', $site_settings);
    }

    private function isRecaptchaEnabled(): bool
    {
        $settings = Setting::first();
        return $settings
            && $settings->recaptcha_enabled
            && $settings->recaptcha_site_key
            && $settings->recaptcha_secret_key;
    }

    private function normalizePhone(string $raw): string
    {
        $phoneCode = optional(DB::table('settings')->first())->phone_code ?? '254';
        $phone = preg_replace('/[^0-9]/', '', $raw);
        if (strlen($phone) <= 10) {
            $phone = $phoneCode . ltrim($phone, '0');
        }
        return $phone;
    }

    protected function validator(array $data)
    {
        // Normalize phone before validation so the unique check matches what gets stored
        $normalizedPhone = isset($data['phone']) ? $this->normalizePhone($data['phone']) : '';

        $rules = [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname'  => ['required', 'string', 'max:255'],
            'phone'     => ['required', 'regex:/^\d{9,15}$/', function ($attribute, $value, $fail) use ($normalizedPhone) {
                if (User::where('phone', $normalizedPhone)->exists()) {
                    $fail('This phone number is already registered.');
                }
            }],
            'email'     => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'terms_and_conditions' => ['accepted'],
        ];

        if ($this->isRecaptchaEnabled()) {
            $rules['g-recaptcha-response'] = ['required', new Recaptcha];
        }

        return Validator::make($data, $rules, [
            'terms_and_conditions.accepted' => 'You must accept the Terms and Conditions.',
            'phone.regex' => 'Enter a valid phone number (digits only, e.g. 712345678).',
        ]);
    }

    protected function create(array $data)
    {
        $phone = $this->normalizePhone($data['phone']);

        $role = Role::where('name', 'Member')->first();
        if ($role == null) {
            $role = Role::where('name', 'User')->first();
        }
        if ($role == null) {
            $role = Role::create(['name' => 'User']);
        }

        try {
            $user = User::create([
                'firstname' => $data['firstname'],
                'lastname'  => $data['lastname'],
                'email'     => $data['email'],
                'phone'     => $phone,
                'password'  => Hash::make($data['password']),
                'status'    => 1,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Race condition: another user registered with same phone/email between validation and insert
            throw \Illuminate\Validation\ValidationException::withMessages([
                'phone' => 'Registration failed. This phone number or email may already be in use. Please try again.',
            ]);
        }

        $user->assignRole($role);

        // Ensure the user's phone is hashed in mpesa_phones so MPesa transactions
        // can be matched even before the first admin interaction with this account.
        $this->createMpesaHashAndMatch($phone, $user->firstname . ' ' . $user->lastname, $user->id);

        return $user;
    }
}
