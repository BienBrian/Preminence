<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validates that inbound M-Pesa callback requests originate from
 * Safaricom's published production IP ranges.
 *
 * Safaricom does not sign their C2B callbacks, so IP whitelisting is
 * the recommended defence. Update $safaricomIps if Safaricom publishes
 * new ranges.
 *
 * In non-production environments the check is skipped so local testing
 * (ngrok, etc.) still works.
 */
class ValidateSafaricomRequest
{
    /**
     * Safaricom published production callback IPs (as of 2025).
     * Source: Safaricom Developer Portal documentation.
     */
    protected array $safaricomIps = [
        '196.201.214.200',
        '196.201.214.206',
        '196.201.213.114',
        '196.201.214.207',
        '196.201.214.208',
        '196.201.213.44',
        '196.201.212.127',
        '196.201.212.138',
        '196.201.212.129',
        '196.201.212.136',
        '196.201.212.74',
        '196.201.212.69',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Skip IP check outside production so developers can test with ngrok/localtunnel
        if (!app()->isProduction()) {
            return $next($request);
        }

        $clientIp = $request->ip();

        if (!in_array($clientIp, $this->safaricomIps, true)) {
            Log::warning('M-Pesa callback rejected: unauthorized IP', [
                'ip'   => $clientIp,
                'path' => $request->path(),
            ]);

            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
