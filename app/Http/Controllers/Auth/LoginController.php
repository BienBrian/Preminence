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
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$fieldType => $login]);
        return $fieldType;
    }
    protected function credentials(Request $request)
    {
        return array_merge($request->only($this->username(), 'password'), ['status' => 1]);

    }

    public function authenticated(Request $request, $user)
    {
        /*
        $ActivityLog = new ActivityLog;
        $ActivityLog->activity = "Login";
        $ActivityLog->user_id = $user->id;
        $ActivityLog->ip_address = $request->getClientIp();
        $ActivityLog->save();*/
    }
    public function username()
    {
        return $this->username;
    }
}
