<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SuperAdminMiddleware
 *
 * Protects routes accessible only to Pisti platform super admins.
 * The 'superadmin' auth guard is fully wired in Phase 8 when the
 * superadmin auth controllers and routes are built.
 *
 * For now this is a stub that will be fleshed out in Phase 8.
 */
class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Phase 8: check auth('superadmin')->check()
        // For now, deny all access to superadmin routes until Phase 8 implements the guard.
        if (!auth('superadmin')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('superadmin.login');
        }

        return $next($request);
    }
}
