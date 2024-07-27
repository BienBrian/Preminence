<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserApprovalStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            if (auth()->user()->approval_status == 2 || auth()->user()->approval_status == 0 || auth()->user()->status == 0) {
                return redirect('dashboard/status'); // Replace '/rejected-page' with the actual URL
            }
        }
        return $next($request);
    }
}
