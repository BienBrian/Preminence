<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureTenantActive Middleware
 *
 * Checks that the resolved tenant's account is in a usable state.
 * Apply to all authenticated dashboard routes:
 *
 *   Route::middleware(['auth', 'tenant.active'])->group(...)
 *
 * Status flow:
 *   trial (valid)       → ✅ allow, show trial banner
 *   active              → ✅ allow
 *   trial (expired)     → redirect to billing page with warning
 *   suspended           → redirect to suspended page
 *   cancelled           → redirect to cancelled page
 *   no tenant context   → allow (superadmin panel, CLI)
 */
class EnsureTenantActive
{
    public function handle(Request $request, Closure $next): Response
    {
        // No tenant context → allow (superadmin panel, API without tenant)
        if (!app()->bound('tenant')) {
            return $next($request);
        }

        $tenant = app('tenant');

        // ── Suspended ─────────────────────────────────────────────────────────
        if ($tenant->status === 'suspended') {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'account_suspended',
                    'message' => 'Your church account has been suspended. Please contact support at support@pisti.co.ke',
                ], 403);
            }

            return redirect()->route('tenant.suspended');
        }

        // ── Cancelled ────────────────────────────────────────────────────────
        if ($tenant->status === 'cancelled') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'account_cancelled'], 403);
            }

            return redirect()->route('tenant.cancelled');
        }

        // ── Trial expired (and not within grace period) ───────────────────────
        if ($tenant->trialExpired() && !$tenant->isWithinGracePeriod()) {
            // Allow billing routes so they can upgrade
            if ($request->routeIs('billing.*')) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'trial_expired',
                    'message' => 'Your trial has ended. Please upgrade to continue.',
                ], 402);
            }

            return redirect()->route('billing.index')
                ->with('warning', 'Your free trial has ended. Choose a plan to continue.');
        }

        // ── Trial expiring soon → flash a banner (within 3 days) ─────────────
        if ($tenant->isOnTrial() && $tenant->trial_ends_at?->diffInDays(now()) <= 3) {
            session()->flash('trial_warning', 'Your trial ends in ' . $tenant->trial_ends_at->diffForHumans() . '. Upgrade now to avoid losing access.');
        }

        return $next($request);
    }
}
