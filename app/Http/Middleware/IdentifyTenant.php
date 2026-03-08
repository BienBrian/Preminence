<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * IdentifyTenant Middleware
 *
 * Resolves the current tenant from the request hostname.
 *
 * ARCHITECTURE:
 * ============
 * Phase 1 (Current): happychurchruiru.org serves Tenant #1 (Happy Church Ruiru)
 *   - happychurchruiru.org → Tenant #1 landing page
 *   - happychurchruiru.org/login → Tenant #1 login
 *   - superadmin.happychurchruiru.org → Superadmin panel
 *
 * Phase 2 (Future): Marketing site on separate domain (e.g., getpisti.com)
 *   - getpisti.com → Marketing/landing page for new signups
 *   - {tenant}.getpisti.com → Tenant subdomains
 *   - superadmin.getpisti.com → Superadmin panel
 *
 * Resolution order:
 *  1. Custom domain match (tenants.custom_domain column)
 *  2. Subdomain slug match (tenants.slug column)
 *  3. Platform domain → Default tenant (happychurchruiru.org → Tenant #1)
 *  4. Local development fallback
 *
 * Excluded subdomains (bypass tenant resolution):
 *  - www.* → Marketing/www redirect
 *  - superadmin.* → Superadmin panel
 *  - admin.* → Superadmin panel alias
 *  - api.* → API endpoints
 */
class IdentifyTenant
{
    /** Subdomains that bypass tenant resolution */
    private const BYPASSED_SUBDOMAINS = ['www', 'admin', 'api', 'staging', 'horizon', 'superadmin'];

    /** Current platform domain - serves Tenant #1 directly */
    private const PRIMARY_PLATFORM_DOMAIN = 'happychurchruiru.org';

    /** Future marketing domains (Phase 2) */
    private const MARKETING_DOMAINS = [
        'getpisti.com',
        'www.getpisti.com',
        'pisti.co.ke',
        'www.pisti.co.ke',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $tenant = null;

        // ── Local development fallback ─────────────────────────────────────────
        if ($this->isLocalEnvironment($host)) {
            return $this->handleLocalDevelopment($request, $next);
        }

        // ── Check for bypassed subdomains (superadmin, www, etc.) ──────────────
        $subdomain = $this->extractSubdomain($host);
        if ($subdomain && in_array($subdomain, self::BYPASSED_SUBDOMAINS)) {
            // These routes handle their own authentication
            return $next($request);
        }

        // ── Try custom domain match first ──────────────────────────────────────
        $tenant = Tenant::where('custom_domain', $host)
            ->whereNotNull('custom_domain')
            ->first();

        // ── Fall back to subdomain slug match ──────────────────────────────────
        if (!$tenant && $subdomain) {
            $tenant = Tenant::where('slug', $subdomain)->first();
        }

        // ── Primary platform domain → Default to Tenant #1 ─────────────────────
        // happychurchruiru.org (without subdomain) serves Tenant #1
        if (!$tenant && $this->isPrimaryPlatformDomain($host)) {
            $tenant = Tenant::find(1);
        }

        // ── Marketing domains → No tenant (future: show marketing site) ────────
        if (!$tenant && $this->isMarketingDomain($host)) {
            // Future: Return marketing site view
            // For now, redirect to primary domain
            return redirect()->away('https://' . self::PRIMARY_PLATFORM_DOMAIN);
        }

        // ── No tenant found → Show coming soon / 404 ───────────────────────────
        if (!$tenant) {
            // Check if this looks like a tenant subdomain that doesn't exist
            if ($subdomain) {
                return response()->view('errors.tenant_not_found', [
                    'subdomain' => $subdomain,
                    'host' => $host,
                ], 404);
            }

            // Unknown domain
            return response()->view('errors.domain_not_configured', [
                'host' => $host,
            ], 404);
        }

        return $this->bindTenant($tenant, $next, $request);
    }

    /**
     * Handle local development environment.
     */
    private function handleLocalDevelopment(Request $request, Closure $next): Response
    {
        $tenant = null;

        // Check for ?__tenant={slug} query parameter for testing
        if ($request->has('__tenant')) {
            $tenant = Tenant::where('slug', $request->get('__tenant'))->first();
        }

        // Default to tenant #1 for local development
        if (!$tenant) {
            $tenant = Tenant::find(1);
        }

        if ($tenant) {
            return $this->bindTenant($tenant, $next, $request);
        }

        return $next($request);
    }

    /**
     * Bind tenant to application context.
     */
    private function bindTenant(Tenant $tenant, Closure $next, Request $request): Response
    {
        // Skip if tenant is not active (except for superadmin routes)
        if (!$tenant->isActive() && !$request->is('superadmin/*')) {
            return response()->view('errors.tenant_inactive', [
                'tenant' => $tenant,
                'status' => $tenant->status,
            ], 403);
        }

        // Bind tenant to the service container
        app()->instance('tenant', $tenant);
        config(['app.tenant_id' => $tenant->id]);

        // Set Spatie Permission team context for proper tenant isolation
        if (app()->bound(\Spatie\Permission\PermissionRegistrar::class)) {
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
        }

        // Share tenant with all Blade views
        view()->share('currentTenant', $tenant);

        return $next($request);
    }

    /**
     * Extract the subdomain portion from a hostname.
     * Examples:
     *   - "grace-community.happychurchruiru.org" → "grace-community"
     *   - "happychurchruiru.org" → null
     *   - "superadmin.happychurchruiru.org" → "superadmin"
     *   - "tenant.getpisti.com" → "tenant"
     */
    private function extractSubdomain(string $host): ?string
    {
        // Remove port if present
        $host = explode(':', $host)[0];

        // Split into parts
        $parts = explode('.', $host);
        $partCount = count($parts);

        // Single part (localhost) or two parts (example.com) - no subdomain
        if ($partCount <= 2) {
            return null;
        }

        // Three parts: could be subdomain.domain.tld OR domain.co.tld
        if ($partCount === 3) {
            $lastTwo = $parts[1] . '.' . $parts[2];

            // Check for known 2-part TLDs (co.ke, co.uk, etc.)
            $twoPartTlds = ['co.ke', 'or.ke', 'go.ke', 'co.uk', 'co.za', 'com.au'];
            if (in_array($lastTwo, $twoPartTlds)) {
                // This is a base domain (e.g., pisti.co.ke)
                return null;
            }

            // This is a subdomain (e.g., church.happychurchruiru.org)
            return $parts[0];
        }

        // Four or more parts: subdomain is always the first part
        // e.g., www.church.happychurchruiru.org → www
        return $parts[0];
    }

    /**
     * Check if host is the primary platform domain (happychurchruiru.org).
     */
    private function isPrimaryPlatformDomain(string $host): bool
    {
        $host = explode(':', $host)[0];

        return $host === self::PRIMARY_PLATFORM_DOMAIN
            || $host === 'www.' . self::PRIMARY_PLATFORM_DOMAIN;
    }

    /**
     * Check if host is a marketing domain (for future Phase 2).
     */
    private function isMarketingDomain(string $host): bool
    {
        $host = explode(':', $host)[0];

        return in_array($host, self::MARKETING_DOMAINS);
    }

    /**
     * Check if running in local development environment.
     */
    private function isLocalEnvironment(string $host): bool
    {
        $localHosts = ['localhost', '127.0.0.1', '::1', '0.0.0.0'];
        $cleanHost = explode(':', $host)[0];

        return in_array($cleanHost, $localHosts)
            || str_ends_with($host, '.local')
            || app()->environment('local', 'testing');
    }
}
