<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->must_change_password) {
            $allowed = [
                'password/force-change',
                'logout',
            ];

            foreach ($allowed as $path) {
                if ($request->is($path)) {
                    return $next($request);
                }
            }

            return redirect('password/force-change');
        }

        return $next($request);
    }
}
