<?php

namespace App\Services;

use App\Mail\GivingStatementMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

/**
 * Giving Report Email Service
 * 
 * Handles email delivery of giving statements with tenant-specific
 * mail configuration support. Also sends SMS alerts to members.
 */
class GivingReportEmailService
{
    /**
     * Send giving statement via email and SMS alert.
     */
    public function send(array $data): array
    {
        $tenant = $data['tenant'];
        $user = $data['user'];
        $config = $data['config'];
        $log = $data['log'];
        $filePath = $data['file_path'];
        $customMessage = $data['custom_message'] ?? null;

        // Configure mail for this tenant
        TenantMailConfigService::configureForTenant($tenant->id);

        $result = [
            'email_sent' => false,
            'sms_sent' => false,
            'sms_error' => null,
        ];

        try {
            // Build email data
            $churchName = $config->church_header_text ?? $tenant->church_name ?? $tenant->name ?? 'Church';
            $emailData = [
                'church_name' => $churchName,
                'member_name' => trim("{$user->firstname} {$user->lastname}"),
                'period_from' => date('F d, Y', strtotime($log->report_period_from)),
                'period_to' => date('F d, Y', strtotime($log->report_period_to)),
                'total_amount' => $log->total_amount,
                'transaction_count' => $log->transaction_count,
                'password_hint' => $log->password_hint,
                'custom_message' => $customMessage,
                'subject' => $config->email_template_subject ?? $this->getDefaultSubject(),
                'body' => $config->email_template_body ?? $this->getDefaultBody(),
            ];

            // Build the mail instance
            $mail = Mail::to($user->email);
            
            // Add reply-to if configured
            if ($config->email_reply_to) {
                $mail->replyTo($config->email_reply_to);
            }

            // Send email with attachment
            $mail->send(new GivingStatementMail(
                $emailData,
                Storage::path($filePath),
                $log->file_name
            ));

            $result['email_sent'] = true;

            // Send SMS alert to member
            if ($user->phone) {
                try {
                    $smsMessage = "Hello {$user->firstname}, your {$churchName} giving statement for {$emailData['period_from']} - {$emailData['period_to']} has been sent to your email ({$user->email}). Total: KES " . number_format($log->total_amount, 2) . ". Check your inbox!";
                    
                    $integrationService = app(\App\Services\IntegrationService::class);
                    $smsResult = $integrationService->sendSms($user->phone, $smsMessage);
                    
                    if ($smsResult !== false) {
                        $result['sms_sent'] = true;
                        
                        // Update usage stats
                        $this->trackSmsUsage($tenant->id);
                    } else {
                        $result['sms_error'] = 'SMS gateway returned false';
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send SMS alert for giving statement', [
                        'tenant_id' => $tenant->id,
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                    $result['sms_error'] = $e->getMessage();
                }
            }

            return $result;
        } finally {
            // Always reset to default configuration
            TenantMailConfigService::resetToDefaults();
        }
    }

    /**
     * Track SMS usage in credits table.
     */
    private function trackSmsUsage(int $tenantId): void
    {
        try {
            \DB::table('credits')
                ->where('tenant_id', $tenantId)
                ->where('type', 'sms')
                ->increment('used', 1);
            
            \DB::table('credits')
                ->where('tenant_id', $tenantId)
                ->where('type', 'sms')
                ->update(['last_used_at' => now()]);
        } catch (\Exception $e) {
            // Ignore errors if table doesn't exist
        }
    }

    /**
     * Get default email subject.
     */
    private function getDefaultSubject(): string
    {
        return 'Your Giving Statement - {{church_name}}';
    }

    /**
     * Get default email body.
     */
    private function getDefaultBody(): string
    {
        return <<<'EOT'
Dear {{member_name}},

Thank you for your faithful giving to {{church_name}}. Attached is your giving statement for the period {{period_from}} to {{period_to}}.

{{custom_message}}

Summary:
- Total Amount: KES {{total_amount}}
- Transactions: {{transaction_count}}

{{password_hint}}

If you have any questions, please contact us.

Blessings,
{{church_name}}
EOT;
    }
}
