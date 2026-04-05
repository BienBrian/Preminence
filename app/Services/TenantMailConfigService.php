<?php

namespace App\Services;

use App\Models\GivingStatementConfig;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

/**
 * Tenant Mail Configuration Service
 * 
 * Manages dynamic mail configuration for tenant-specific email settings.
 * Allows each tenant to use their own SMTP or email service credentials
 * for sending giving statements and other module-specific emails.
 */
class TenantMailConfigService
{
    /**
     * Configure mail for a specific tenant.
     * This temporarily overrides the system mail configuration.
     */
    public static function configureForTenant(int $tenantId): bool
    {
        $config = GivingStatementConfig::forTenant($tenantId);
        
        if (!$config->isEmailConfigured()) {
            return false;
        }

        // Use default system mail
        if ($config->email_driver === 'default' || $config->email_driver === null) {
            return true; // Use existing system configuration
        }

        $mailConfig = $config->getMailConfig();

        // Override mail configuration dynamically
        Config::set('mail.default', $mailConfig['driver']);
        Config::set('mail.from.address', $mailConfig['from']['address']);
        Config::set('mail.from.name', $mailConfig['from']['name']);

        // Configure SMTP
        if ($mailConfig['driver'] === 'smtp' && isset($mailConfig['host'])) {
            Config::set('mail.mailers.smtp.host', $mailConfig['host']);
            Config::set('mail.mailers.smtp.port', $mailConfig['port']);
            Config::set('mail.mailers.smtp.username', $mailConfig['username']);
            Config::set('mail.mailers.smtp.password', $mailConfig['password']);
            Config::set('mail.mailers.smtp.encryption', $mailConfig['encryption']);
        }

        // Configure Mailgun
        if ($mailConfig['driver'] === 'mailgun' && isset($mailConfig['mailgun'])) {
            Config::set('services.mailgun.domain', $mailConfig['mailgun']['domain']);
            Config::set('services.mailgun.secret', $mailConfig['mailgun']['secret']);
        }

        // Configure Postmark
        if ($mailConfig['driver'] === 'postmark' && isset($mailConfig['postmark'])) {
            Config::set('services.postmark.token', $mailConfig['postmark']['token']);
        }

        // Configure SES
        if ($mailConfig['driver'] === 'ses' && isset($mailConfig['ses'])) {
            Config::set('services.ses.key', $mailConfig['ses']['key']);
            Config::set('services.ses.secret', $mailConfig['ses']['secret']);
            Config::set('services.ses.region', $mailConfig['ses']['region']);
        }

        // Purge mail manager to apply new configuration
        Mail::purge();

        return true;
    }

    /**
     * Reset mail configuration to system defaults.
     */
    public static function resetToDefaults(): void
    {
        // Reload configuration from config files
        $defaultConfig = require config_path('mail.php');
        
        Config::set('mail', $defaultConfig);
        
        // Purge mail manager
        Mail::purge();
    }

    /**
     * Test email configuration by sending a test email.
     */
    public static function testConfiguration(int $tenantId, string $testEmail): array
    {
        $config = GivingStatementConfig::forTenant($tenantId);
        
        try {
            // Configure mail for tenant
            if (!self::configureForTenant($tenantId)) {
                return [
                    'success' => false,
                    'message' => 'Email is not configured for this tenant.',
                ];
            }

            // Send test email
            Mail::raw(
                "This is a test email from your Giving Statements module.\n\n" .
                "If you received this, your email configuration is working correctly!\n\n" .
                "Configuration used:\n" .
                "- Driver: " . ($config->email_driver ?? 'default') . "\n" .
                "- From: " . ($config->email_from_address ?? config('mail.from.address')) . "\n\n" .
                "Sent at: " . now()->format('Y-m-d H:i:s'),
                function ($message) use ($testEmail, $config) {
                    $message->to($testEmail)
                        ->subject('Giving Statements - Email Configuration Test');
                    
                    if ($config->email_reply_to) {
                        $message->replyTo($config->email_reply_to);
                    }
                }
            );

            // Mark as tested successfully
            $config->markAsTested();

            // Reset to defaults
            self::resetToDefaults();

            return [
                'success' => true,
                'message' => 'Test email sent successfully!',
            ];
        } catch (\Exception $e) {
            // Mark test as failed
            $config->markTestFailed($e->getMessage());

            // Reset to defaults
            self::resetToDefaults();

            return [
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get available email drivers.
     */
    public static function getAvailableDrivers(): array
    {
        return [
            'default' => 'Use System Default Mail',
            'smtp' => 'SMTP Server',
            'mailgun' => 'Mailgun',
            'postmark' => 'Postmark',
            'sendgrid' => 'SendGrid (via SMTP)',
            'ses' => 'Amazon SES',
            'log' => 'Log Only (Testing)',
        ];
    }

    /**
     * Get encryption options.
     */
    public static function getEncryptionOptions(): array
    {
        return [
            'tls' => 'TLS (Recommended)',
            'ssl' => 'SSL',
            'none' => 'None (Not Recommended)',
        ];
    }
}
