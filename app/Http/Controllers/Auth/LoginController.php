<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Providers\RouteServiceProvider;
use App\Rules\Recaptcha;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    protected $username;
    public function __construct()
    {
        $this->middleware('guest')->except('logout');

        $this->username = $this->findUsername();

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

    protected function validateLogin(Request $request)
    {
        $rules = [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ];

        if ($this->isRecaptchaEnabled()) {
            $rules['g-recaptcha-response'] = ['required', new Recaptcha];
        }

        $request->validate($rules);
    }
    public function findUsername()
    {
        $login = request()->input('email');

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        // Non-email input: treat as phone number and normalize
        $phoneCode = optional(DB::table('settings')->first())->phone_code ?? '254';
        $phone = preg_replace('/[^0-9]/', '', $login ?? '');
        if ($phone && strlen($phone) <= 10) {
            $phone = $phoneCode . ltrim($phone, '0');
        }
        request()->merge(['phone' => $phone]);
        return 'phone';
    }
    protected function credentials(Request $request)
    {
        $credentials = array_merge($request->only($this->username(), 'password'), ['status' => 1]);
        
        if ($tenantId = config('app.tenant_id')) {
            $credentials['tenant_id'] = $tenantId;
        }
        
        return $credentials;
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        // Always show the error on the 'email' field (the form input name)
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    public function authenticated(Request $request, $user)
    {
        // Ensure Spatie Permission team context is set for the authenticated user
        if ($user->tenant_id) {
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);
        }
    }
    public function username()
    {
        return $this->username;
    }
}
