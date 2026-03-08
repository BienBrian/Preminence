<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\IntegrationService;

class TestSmsIntegration extends Command
{
    protected $signature = 'sms:test {phone?} {--tenant=1}';
    protected $description = 'Test SMS integration - check credits and optionally send test message';

    public function handle(): int
    {
        $tenantId = $this->option('tenant');
        config(['app.tenant_id' => $tenantId]);
        
        $this->info("Testing SMS integration for tenant: {$tenantId}");
        $this->line('');
        
        $service = app(IntegrationService::class);
        
        // Get SMS config
        $config = $service->getSmsConfig();
        $this->info('SMS Configuration:');
        $this->table(['Key', 'Value'], [
            ['URL', $config['url'] ?: 'NOT SET'],
            ['API Key', $config['api_key'] ? substr($config['api_key'], 0, 10) . '...' : 'NOT SET'],
            ['Partner ID', $config['partner_id'] ?: 'NOT SET'],
            ['Short Code', $config['short_code'] ?: 'NOT SET'],
        ]);
        $this->line('');
        
        // Check credits
        $this->info('Checking SMS credits from API...');
        $credits = $service->checkSmsCredits();
        
        if ($credits === false) {
            $this->error('Failed to check credits from API');
        } else {
            $this->info('Credits Response:');
            print_r($credits);
            
            $balance = $credits['balance'] 
                ?? $credits['credit'] 
                ?? $credits['credits'] 
                ?? $credits['credits_remaining'] 
                ?? $credits['data']['balance'] 
                ?? 'unknown';
            
            $this->line('');
            $this->info("SMS Credits Balance: {$balance}");
        }
        
        // Send test message if phone provided
        $phone = $this->argument('phone');
        if ($phone) {
            $this->line('');
            $this->info("Sending test SMS to: {$phone}");
            
            $result = $service->sendSms($phone, "Test message from ChurchApp at " . now());
            
            $this->line('');
            if ($result === false) {
                $this->error('SMS send failed!');
                return 1;
            } else {
                $this->info('SMS send result:');
                print_r($result);
                return 0;
            }
        }
        
        return 0;
    }
}
