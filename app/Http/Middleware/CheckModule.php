<?php

namespace App\Http\Middleware;

use App\Services\ModuleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckModule Middleware
 *
 * Gates access to routes based on whether a feature module is enabled for the
 * current tenant. Apply to route groups per module:
 *
 *   Route::middleware(['auth', 'tenant.active', 'module:finance'])->group(...)
 *
 * Returns:
 *   - JSON 403 for API requests
 *   - Redirect to upgrade page for web requests, with a contextual message
 */
class CheckModule
{
    public function __construct(private ModuleService $moduleService)
    {
    }

    public function handle(Request $request, Closure $next, string $module): Response
    {
        if ($this->moduleService->isEnabled($module)) {
            return $next($request);
        }

        // ── Module is disabled for this tenant ────────────────────────────────
        $label = $this->moduleLabel($module);

        if ($request->expectsJson()) {
            return response()->json([
                'error'   => 'module_disabled',
                'module'  => $module,
                'message' => "The {$label} module is not enabled on your plan. Please upgrade to access this feature.",
            ], 403);
        }

        // Redirect to module locked page
        return redirect()
            ->route('billing.module_locked', ['module' => $module]);
    }

    /**
     * Human-readable label for a module key.
     */
    private function moduleLabel(string $module): string
    {
        return match ($module) {
            'people'       => 'People & Members',
            'attendance'   => 'Attendance',
            'finance'      => 'Finance & Giving',
            'mpesa'        => 'M-Pesa Integration',
            'sms'          => 'SMS Messaging',
            'email'        => 'Email Campaigns',
            'events'       => 'Events & Seminars',
            'spiritual'    => 'Spiritual Content',
            'website'      => 'Church Website',
            'shop'         => 'Shop & Products',
            'media'        => 'Media Library',
            'reports'      => 'Reports & Analytics',
            'discipleship' => 'Discipleship',
            'api_access'   => 'API Access',
            default        => ucfirst($module),
        };
    }
}
