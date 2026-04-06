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

        // Primary: token-based validation.
        // The hosting proxy rewrites the real Safaricom IP to an internal 10.x address,
        // making IP whitelisting impossible. We therefore embed a secret token in the
        // registered callback URL (e.g. ?_t=TOKEN). Safaricom calls exactly the URL
        // that was registered, so only Safaricom (and whoever controls the Safaricom
        // portal) can invoke this endpoint.
        $configuredToken = env('MPESA_CALLBACK_TOKEN');
        if (!empty($configuredToken) && hash_equals($configuredToken, (string) $request->query('_t', ''))) {
            return $next($request);
        }

        // Fallback: IP whitelist (works when the hosting proxy forwards real IPs).
        // Build candidate IP list from all forwarding headers.
        $candidates = array_filter(array_unique([
            $request->ip(),
            $request->header('X-Real-IP'),
            ...$this->parseForwardedFor($request->header('X-Forwarded-For', '')),
        ]));

        foreach ($candidates as $ip) {
            if (in_array($ip, $this->safaricomIps, true)) {
                return $next($request);
            }
        }

        Log::warning('M-Pesa callback rejected: token mismatch and no Safaricom IP found', [
            'resolved_ip'     => $request->ip(),
            'x_forwarded_for' => $request->header('X-Forwarded-For'),
            'x_real_ip'       => $request->header('X-Real-IP'),
            'has_token'       => $request->has('_t'),
            'path'            => $request->path(),
        ]);

        return response()->json(['error' => 'Unauthorized'], 403);
    }

    private function parseForwardedFor(string $header): array
    {
        if (empty($header)) {
            return [];
        }
        return array_map('trim', explode(',', $header));
    }
}
