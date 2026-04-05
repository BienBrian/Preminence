<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Giving Statement Configuration
 * 
 * Tenant-specific settings for generating giving statements.
 */
class GivingStatementConfig extends Model
{
    use HasFactory;

    protected $table = 'giving_statement_configs';

    protected $fillable = [
        'tenant_id',
        'default_password_type',
        'custom_password_code',
        'email_template_subject',
        'email_template_body',
        'include_thank_you_note',
        'thank_you_note',
        'church_header_text',
        'report_title',
        'enable_bulk_generation',
        'enable_email_delivery',
        'enable_sms_otp',
        'report_format',
        'fiscal_year_start',
        'pdf_owner_password',
        // Email credentials
        'email_driver',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'email_from_address',
        'email_from_name',
        'email_reply_to',
        'email_service_key',
        'email_service_secret',
        'email_service_domain',
        'email_service_region',
        'test_email_address',
        'email_config_tested_at',
        'email_config_test_error',
    ];

    protected $casts = [
        'include_thank_you_note' => 'boolean',
        'enable_bulk_generation' => 'boolean',
        'enable_email_delivery' => 'boolean',
        'enable_sms_otp' => 'boolean',
        'fiscal_year_start' => 'integer',
        'smtp_port' => 'integer',
        'email_config_tested_at' => 'datetime',
    ];

    /**
     * The attributes that should be encrypted.
     * Note: Laravel automatically encrypts these when using 'encrypted' cast
     */
    protected $encrypted = [
        'smtp_password',
        'email_service_key',
        'email_service_secret',
    ];

    /**
     * Get the tenant this config belongs to.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get default config for a tenant (creates if not exists).
     */
    public static function forTenant(int $tenantId): self
    {
        return self::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'default_password_type' => 'phone',
                'email_template_subject' => 'Your Giving Statement from {{ $churchName }}',
                'thank_you_note' => 'Thank you for your faithful giving. Your generosity makes a difference in our church and community.',
                'report_title' => 'Giving Statement',
                'fiscal_year_start' => 1,
            ]
        );
    }

    /**
     * Check if a specific password type is enabled.
     */
    public function isPasswordTypeEnabled(string $type): bool
    {
        if ($type === 'sms_otp') {
            return $this->enable_sms_otp;
        }
        return true; // Other types are always available
    }

    /**
     * Get fiscal year start month name.
     */
    public function getFiscalYearStartMonth(): string
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        return $months[$this->fiscal_year_start] ?? 'January';
    }

    /**
     * Get default thank you note (with fallback).
     */
    public function getThankYouNote(): string
    {
        return $this->thank_you_note ?? 'Thank you for your generous giving.';
    }

    /**
     * Get email template with variables replaced.
     */
    public function renderEmailTemplate(array $variables): string
    {
        $template = $this->email_template_body ?? $this->getDefaultEmailTemplate();
        
        foreach ($variables as $key => $value) {
            $template = str_replace('{{ $' . $key . ' }}', $value, $template);
            $template = str_replace('{{ $' . $key . '}}', $value, $template);
        }
        
        return $template;
    }

    /**
     * Get default email template.
     */
    private function getDefaultEmailTemplate(): string
    {
        return <<<HTML
<p>Dear {{ $memberName }},</p>

<p>Thank you for your faithful giving to {{ $churchName }}. Please find your {{ $reportPeriod }} giving statement attached.</p>

<p><strong>Total Giving: {{ $totalAmount }}</strong></p>

<p>{{ $thankYouNote }}</p>

<p>The attached PDF is password protected for your security.</p>
<p><em>Password hint: {{ $passwordHint }}</em></p>

<p>Blessings,<br>
{{ $churchName }} Finance Team</p>
HTML;
    }

    /**
     * Check if email is properly configured.
     */
    public function isEmailConfigured(): bool
    {
        if (!$this->enable_email_delivery) {
            return false;
        }

        // If using default system mail, check if system mail is configured
        if ($this->email_driver === 'default' || $this->email_driver === null) {
            return true; // Assume system mail is configured
        }

        // Check SMTP configuration
        if ($this->email_driver === 'smtp') {
            return !empty($this->smtp_host) 
                && !empty($this->smtp_port) 
                && !empty($this->smtp_username)
                && !empty($this->email_from_address);
        }

        // Check service-based configuration
        if (in_array($this->email_driver, ['mailgun', 'sendgrid', 'postmark', 'ses'])) {
            return !empty($this->email_service_key) 
                && !empty($this->email_from_address);
        }

        return false;
    }

    /**
     * Get sender email address.
     */
    public function getFromAddress(): ?string
    {
        return $this->email_from_address ?? null;
    }

    /**
     * Get sender name.
     */
    public function getFromName(string $defaultName = ''): string
    {
        return $this->email_from_name ?: $defaultName;
    }

    /**
     * Get mail configuration as array for Laravel Mail.
     */
    public function getMailConfig(): array
    {
        $config = [
            'driver' => $this->email_driver === 'default' ? config('mail.default') : $this->email_driver,
            'from' => [
                'address' => $this->email_from_address ?: config('mail.from.address'),
                'name' => $this->email_from_name ?: config('mail.from.name'),
            ],
        ];

        // Add SMTP settings if applicable
        if ($this->email_driver === 'smtp') {
            $config['host'] = $this->smtp_host;
            $config['port'] = $this->smtp_port;
            $config['username'] = $this->smtp_username;
            $config['password'] = $this->smtp_password;
            $config['encryption'] = $this->smtp_encryption;
        }

        // Add service-specific settings
        if ($this->email_driver === 'mailgun') {
            $config['mailgun'] = [
                'domain' => $this->email_service_domain,
                'secret' => $this->email_service_key,
            ];
        }

        if ($this->email_driver === 'postmark') {
            $config['postmark'] = [
                'token' => $this->email_service_key,
            ];
        }

        if ($this->email_driver === 'ses') {
            $config['ses'] = [
                'key' => $this->email_service_key,
                'secret' => $this->email_service_secret,
                'region' => $this->email_service_region ?: 'us-east-1',
            ];
        }

        return $config;
    }

    /**
     * Mark email configuration as tested successfully.
     */
    public function markAsTested(): void
    {
        $this->update([
            'email_config_tested_at' => now(),
            'email_config_test_error' => null,
        ]);
    }

    /**
     * Mark email configuration test as failed.
     */
    public function markTestFailed(string $error): void
    {
        $this->update([
            'email_config_tested_at' => now(),
            'email_config_test_error' => $error,
        ]);
    }
}
