<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }
        
        // Check if this is a superadmin route by checking the subdomain
        $host = $request->getHost();
        if (str_starts_with($host, 'superadmin.')) {
            return route('superadmin.login');
        }
        
        return route('login');
    }
}
