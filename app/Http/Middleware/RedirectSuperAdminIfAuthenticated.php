<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectSuperAdminIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        // If accessing superadmin routes and already logged in as superadmin
        if (Auth::guard('superadmin')->check()) {
            // Redirect to superadmin dashboard (skip login page)
            // But don't redirect if already on dashboard to avoid loops
            if (!$request->is('dashboard*') && $request->path() !== '/') {
                return redirect('/dashboard');
            }
        }

        return $next($request);
    }
}
