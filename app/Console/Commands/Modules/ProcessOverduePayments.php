<?php

namespace App\Console\Commands\Modules;

use App\Services\Modules\BillingScheduleService;
use Illuminate\Console\Command;

class ProcessOverduePayments extends Command
{
    protected $signature = 'modules:process-overdue
                            {--grace-period=3 : Grace period in days}
                            {--dry-run : Show what would happen without making changes}
                            {--suspend : Suspend modules after max retries}';

    protected $description = 'Process overdue module payments and send reminders';

    private BillingScheduleService $scheduleService;

    public function __construct(BillingScheduleService $scheduleService)
    {
        parent::__construct();
        $this->scheduleService = $scheduleService;
    }

    public function handle(): int
    {
        $gracePeriod = (int) $this->option('grace-period');
        $isDryRun = $this->option('dry-run');
        $shouldSuspend = $this->option('suspend');
        
        $this->info('Processing overdue module payments...');
        $this->info("Grace period: {$gracePeriod} days");
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Get overdue billings
        $overdue = $this->scheduleService->getOverdueBillings($gracePeriod);

        $this->info("Found {$overdue->count()} overdue subscriptions");

        if ($overdue->isEmpty()) {
            $this->info('No overdue payments to process.');
            return self::SUCCESS;
        }

        // Group by tenant for summary
        $byTenant = $overdue->groupBy('tenant_id');
        
        $this->info("\nAffected tenants: {$byTenant->count()}");

        // Show details
        $rows = $overdue->map(fn($sub) => [
            $sub->id,
            $sub->tenant->name ?? 'Unknown',
            $sub->module->name ?? $sub->module_key,
            $sub->next_billing_at->format('Y-m-d'),
            $sub->next_billing_at->diffInDays(now()) . ' days overdue',
            'KES ' . number_format($sub->price, 2),
        ]);

        $this->table(
            ['ID', 'Tenant', 'Module', 'Due Date', 'Overdue', 'Amount'],
            $rows
        );

        if ($isDryRun) {
            $this->info('\nDry run complete. No changes made.');
            return self::SUCCESS;
        }

        if (!$this->confirm('Do you want to process these overdue payments?')) {
            $this->info('Cancelled.');
            return self::SUCCESS;
        }

        // Process each overdue subscription
        $processed = 0;
        $suspended = 0;
        $errors = 0;

        foreach ($overdue as $subscription) {
            try {
                // Get retry count from metadata
                $retryCount = $subscription->metadata['billing_retries'] ?? 0;
                $maxRetries = config('modules.billing.retry_attempts', 3);

                if ($retryCount >= $maxRetries && $shouldSuspend) {
                    // Suspend the subscription
                    $subscription->update([
                        'status' => 'suspended',
                        'suspended_at' => now(),
                        'suspension_reason' => 'payment_overdue',
                    ]);

                    // Disable the module
                    \App\Models\TenantModule::where('tenant_id', $subscription->tenant_id)
                        ->where('module', $subscription->module_key)
                        ->update(['is_enabled' => false]);

                    $suspended++;
                    
                    $this->warn("Suspended: {$subscription->module_key} for tenant {$subscription->tenant_id}");
                } else {
                    // Increment retry and schedule next attempt
                    $subscription->update([
                        'metadata' => array_merge(
                            $subscription->metadata ?? [],
                            ['billing_retries' => $retryCount + 1]
                        ),
                    ]);
                    
                    // Send reminder notification
                    $this->notifyTenant($subscription);
                    
                    $processed++;
                }
            } catch (\Exception $e) {
                $this->error("Error processing subscription {$subscription->id}: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->info("\nResults:");
        $this->table(
            ['Action', 'Count'],
            [
                ['Reminders Sent', $processed],
                ['Suspended', $suspended],
                ['Errors', $errors],
            ]
        );

        return self::SUCCESS;
    }

    private function notifyTenant($subscription): void
    {
        $tenant = $subscription->tenant;
        $owner = $tenant->owner;
        
        if ($owner) {
            // Notification would be sent here
            $this->info("Notification would be sent to: {$owner->email}");
        }
    }
}
