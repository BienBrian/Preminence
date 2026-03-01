<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Rules\Recaptcha;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    use RegistersUsers;

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

    protected function validator(array $data)
    {
        $rules = [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname'  => ['required', 'string', 'max:255'],
            'phone'     => ['required', 'regex:/^\d{9,15}$/', 'unique:users,phone'],
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
        $phoneCode = optional(DB::table('settings')->first())->phone_code ?? '254';
        $phone = preg_replace('/[^0-9]/', '', $data['phone']);
        if (strlen($phone) <= 10) {
            $phone = $phoneCode . ltrim($phone, '0');
        }

        $role = Role::where('name', 'Member')->first();
        if ($role == null) {
            $role = Role::where('name', 'User')->first();
        }
        if ($role == null) {
            $role = Role::create(['name' => 'User']);
        }

        $user = User::create([
            'firstname' => $data['firstname'],
            'lastname'  => $data['lastname'],
            'email'     => $data['email'],
            'phone'     => $phone,
            'password'  => Hash::make($data['password']),
        ]);
        $user->status = 1;
        $user->save();
        $user->assignRole($role);

        return $user;
    }
}
