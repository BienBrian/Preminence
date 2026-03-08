<?php

namespace App\Http\Controllers\SuperAdmin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Where to redirect superadmins after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Apply custom guest middleware for superadmin
        $this->middleware(\App\Http\Middleware\RedirectSuperAdminIfAuthenticated::class)->only(['showLoginForm', 'login']);
    }

    /**
     * Show the superadmin login form.
     */
    public function showLoginForm()
    {
        return view('superadmin.auth.login');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Attempt to log in as superadmin
        if (Auth::guard('superadmin')->attempt(
            $request->only('email', 'password'),
            $request->filled('remember')
        )) {
            $request->session()->regenerate();

            // Record login metadata
            $superAdmin = Auth::guard('superadmin')->user();
            $superAdmin->recordLogin($request->ip());

            // Redirect to dashboard using named route
            return redirect()->route('superadmin.dashboard');
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => 'These credentials do not match our records.']);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::guard('superadmin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login');
    }

    /**
     * Get the guard to be used during authentication.
     */
    protected function guard()
    {
        return Auth::guard('superadmin');
    }
}
