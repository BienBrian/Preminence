<?php

namespace App\Services;

use App\Models\Integration;
use Illuminate\Support\Facades\Log;

/**
 * IntegrationService — Phase 3: Integration Isolation
 *
 * Central service for resolving SMS, Email, and Mpesa credentials from the
 * per-tenant `integrations` table instead of global .env values.
 *
 * Usage in controllers/commands:
 *   app(IntegrationService::class)->sendSms($phone, $message);
 *   app(IntegrationService::class)->applyEmailConfig();
 *   app(IntegrationService::class)->getMpesaConfig();
 *
 * Falls back to .env values when no DB integration is found, ensuring
 * zero-downtime compatibility with the existing single-tenant setup.
 */
class IntegrationService
{
    /** Per-request cache so the DB is only hit once per type */
    private array $cache = [];

    // ─────────────────────────────────────────────────────────────
    // Config Loaders
    // ─────────────────────────────────────────────────────────────

    /**
     * Load the active default SMS integration for the current tenant.
     * Returns a config array with: url, api_key, partner_id, short_code.
     */
    public function getSmsConfig(): array
    {
        return $this->cache['sms'] ??= $this->resolveConfig('sms', [
            'url'        => env('SMS_URL', ''),
            'api_key'    => env('SMS_API_KEY', ''),
            'partner_id' => env('SMS_PARTNER_ID', ''),
            'short_code' => env('SMS_SHORT_CODE', ''),
        ]);
    }

    /**
     * Load the active default Mpesa integration for the current tenant.
     * Returns a config array with Safaricom API keys.
     */
    public function getMpesaConfig(): array
    {
        return $this->cache['mpesa'] ??= $this->resolveConfig('mpesa', [
            'shortcode'          => env('MPESA_SHORTCODE', ''),
            'consumer_key'       => env('MPESA_CONSUMER_KEY', ''),
            'consumer_secret'    => env('MPESA_CONSUMER_SECRET', ''),
            'passkey'            => env('MPESA_LNMO_PASSKEY', ''),
            'stk_url'            => env('MPESA_STK_URL', 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'),
            'oauth_url'          => env('MPESA_OAUTH_URL', 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'),
            'c2b_register_url'   => env('MPESA_C2B_REGISTER_URL', 'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl'),
            'callback_base_url'  => env('MPESA_CALLBACK_BASE_URL', ''),
            'account_ref'        => env('MPESA_ACCOUNT_REF', 'Church Donation'),
        ]);
    }

    /**
     * Load the active default Email integration for the current tenant.
     */
    public function getEmailConfig(): array
    {
        return $this->cache['email'] ??= $this->resolveConfig('email', [
            'host'         => env('MAIL_HOST', ''),
            'port'         => env('MAIL_PORT', 465),
            'username'     => env('MAIL_USERNAME', ''),
            'password'     => env('MAIL_PASSWORD', ''),
            'encryption'   => env('MAIL_ENCRYPTION', 'ssl'),
            'from_address' => env('MAIL_FROM_ADDRESS', ''),
            'from_name'    => env('MAIL_FROM_NAME', config('app.name', 'Church App')),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // SMS Sending
    // ─────────────────────────────────────────────────────────────

    /**
     * Send an SMS using the current tenant's configured SMS integration.
     *
     * @param  string  $phone    International format e.g. 254712345678
     * @param  string  $message  Message body
     * @return array|false       Decoded JSON response from SMS API, or false on curl error
     */
    public function sendSms(string $phone, string $message): mixed
    {
        $config = $this->getSmsConfig();
        $tenantId = config('app.tenant_id');

        if (empty($config['url']) || empty($config['api_key'])) {
            Log::warning('IntegrationService::sendSms — no SMS integration configured for tenant ' . $tenantId);
            return false;
        }

        // Build query parameters for GET request
        // Tenasms/AdvantaSMS requires apikey as query parameter, not Authorization header
        $queryParams = http_build_query([
            'partnerID' => $config['partner_id'],
            'apikey'    => $config['api_key'],
            'message'   => $message,
            'shortcode' => $config['short_code'],
            'mobile'    => $phone,
        ]);
        
        $fullUrl = $config['url'] . '?' . $queryParams;

        Log::debug('IntegrationService::sendSms — Sending SMS', [
            'tenant_id' => $tenantId,
            'phone' => $phone,
            'url' => $config['url'],
            'full_url' => $config['url'] . '?partnerID=' . $config['partner_id'] . '&apikey=***&mobile=***',
            'shortcode' => $config['short_code'],
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $fullUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
        ]);

        $response = curl_exec($curl);
        $error    = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($error) {
            Log::error('IntegrationService::sendSms curl error: ' . $error);
            return false;
        }

        $decoded = json_decode($response, true);
        
        Log::debug('IntegrationService::sendSms — Response', [
            'tenant_id' => $tenantId,
            'phone' => $phone,
            'http_code' => $httpCode,
            'response' => $decoded ?? $response,
        ]);

        // Check if response indicates success (depends on SMS provider)
        if ($httpCode !== 200) {
            Log::error('IntegrationService::sendSms — HTTP error', [
                'http_code' => $httpCode,
                'response' => $response,
            ]);
            return false;
        }

        // Check for API-level errors (specific to Tenasms/AdvantaSMS format)
        if (is_array($decoded)) {
            // Check for common error indicators
            if (isset($decoded['error']) || isset($decoded['Error'])) {
                Log::error('IntegrationService::sendSms — API error', [
                    'response' => $decoded,
                ]);
                return false;
            }
            
            // Check for credit-related responses
            if (isset($decoded['credits_remaining'])) {
                Log::info('IntegrationService::sendSms — Credits remaining', [
                    'credits' => $decoded['credits_remaining'],
                ]);
            }
        }

        return $decoded ?? $response;
    }

    /**
     * Check SMS credits from the provider API.
     * For Tenasms/AdvantaSMS: GET /api/services/getcredits
     * Note: Not all SMS providers have a credits endpoint. Some return credits 
     * in the send response or don't expose credits at all.
     *
     * @return array|false ['balance' => int, 'status' => string] or false on error
     */
    public function checkSmsCredits(): mixed
    {
        $config = $this->getSmsConfig();
        $tenantId = config('app.tenant_id');

        if (empty($config['url']) || empty($config['api_key'])) {
            Log::warning('IntegrationService::checkSmsCredits — no SMS integration configured');
            return false;
        }

        // Try multiple possible credits endpoint patterns
        $baseUrl = str_replace('/sendsms', '', $config['url']);
        $possibleEndpoints = [
            $baseUrl . '/getcredits',
            $baseUrl . '/getbalance',
            $baseUrl . '/balance',
            $baseUrl . '/credits',
            'https://sms.tenasms.com/api/services/getcredits',
        ];
        
        foreach ($possibleEndpoints as $creditsUrl) {
            // Tenasms/AdvantaSMS requires apikey as query parameter
            $queryParams = http_build_query([
                'partnerID' => $config['partner_id'],
                'apikey'    => $config['api_key'],
            ]);
            
            $fullUrl = $creditsUrl . '?' . $queryParams;

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => $fullUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpCode === 200) {
                $decoded = json_decode($response, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    Log::debug('IntegrationService::checkSmsCredits — Success', [
                        'tenant_id' => $tenantId,
                        'endpoint' => $creditsUrl,
                        'response' => $decoded,
                    ]);
                    return $decoded;
                }
            }
        }

        // If no credits endpoint works, return unknown but don't fail
        // The actual send will report if credits are insufficient
        Log::debug('IntegrationService::checkSmsCredits — No credits endpoint available', [
            'tenant_id' => $tenantId,
        ]);
        
        return ['balance' => null, 'status' => 'unknown'];
    }

    // ─────────────────────────────────────────────────────────────
    // Email Config
    // ─────────────────────────────────────────────────────────────

    /**
     * Dynamically override Laravel's runtime mail config with the current
     * tenant's email integration settings. Call this before any Mail::send().
     *
     * This does NOT persist across requests — it only affects the current request/job.
     */
    public function applyEmailConfig(): void
    {
        $cfg = $this->getEmailConfig();

        if (empty($cfg['host'])) {
            return; // No tenant email config — fall through to default .env settings
        }

        config([
            'mail.mailers.smtp.host'       => $cfg['host'],
            'mail.mailers.smtp.port'       => $cfg['port'],
            'mail.mailers.smtp.username'   => $cfg['username'],
            'mail.mailers.smtp.password'   => $cfg['password'],
            'mail.mailers.smtp.encryption' => $cfg['encryption'] ?: null,
            'mail.from.address'            => $cfg['from_address'],
            'mail.from.name'               => $cfg['from_name'],
        ]);

        // Force the mailer to re-resolve so the new config is picked up
        app()->forgetInstance('mailer');
        app()->forgetInstance('swift.mailer');
        app()->forgetInstance('swift.transport');
    }

    // ─────────────────────────────────────────────────────────────
    // Mpesa Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Generate the Mpesa LNMO (Lipa Na Mpesa Online) password.
     * Replaces: base64_encode(env('MPESA_SHORTCODE') . env('MPESA_LNMO_PASSKEY') . $timestamp)
     */
    public function lipaNaMpesaPassword(): string
    {
        $config    = $this->getMpesaConfig();
        $timestamp = now()->format('YmdHms');
        return base64_encode($config['shortcode'] . $config['passkey'] . $timestamp);
    }

    /**
     * Obtain a Safaricom OAuth access token using the tenant's credentials.
     */
    public function generateMpesaAccessToken(): string
    {
        $config      = $this->getMpesaConfig();
        $credentials = base64_encode($config['consumer_key'] . ':' . $config['consumer_secret']);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $config['oauth_url'],
            CURLOPT_HTTPHEADER     => ['Authorization: Basic ' . $credentials],
            CURLOPT_HEADER         => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response    = curl_exec($curl);
        curl_close($curl);

        $decoded = json_decode($response);
        return $decoded->access_token ?? '';
    }

    /**
     * Resolve the tenant's `Integration` model for a given shortcode.
     * Used by the Mpesa C2B callback to route to the correct tenant.
     *
     * @return \App\Models\Integration|null
     */
    public function resolveMpesaIntegrationByShortcode(string $shortcode): ?Integration
    {
        return Integration::withoutTenantScope()
            ->where('type', 'mpesa')
            ->where('is_active', true)
            ->get()
            ->first(function (Integration $integration) use ($shortcode) {
                return ($integration->getConfigValue('shortcode') ?? '') === $shortcode;
            });
    }

    // ─────────────────────────────────────────────────────────────
    // Internal
    // ─────────────────────────────────────────────────────────────

    /**
     * Load the config array from the active default integration for the given type.
     * Returns $fallback if no integration record exists or .env fallback is applicable.
     */
    private function resolveConfig(string $type, array $fallback): array
    {
        try {
            $integration = Integration::active()->default()->ofType($type)->first();

            if ($integration) {
                $cfg = $integration->config;
                if (is_array($cfg) && !empty(array_filter($cfg))) {
                    return $cfg;
                }
            }
        } catch (\Exception $e) {
            // Table may not exist yet during migrations — fall through to env
            Log::debug('IntegrationService::resolveConfig fallback for ' . $type . ': ' . $e->getMessage());
        }

        return $fallback;
    }
}
