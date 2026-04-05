<?php

namespace App\Console\Commands\Modules;

use App\Services\Modules\ModuleBillingService;
use Illuminate\Console\Command;

class ProcessExpiredTrials extends Command
{
    protected $signature = 'modules:process-trials
                            {--dry-run : Show what would happen without making changes}
                            {--notify : Send email notifications to tenants}';

    protected $description = 'Process expired module trials - convert to paid or suspend';

    private ModuleBillingService $billingService;

    public function __construct(ModuleBillingService $billingService)
    {
        parent::__construct();
        $this->billingService = $billingService;
    }

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $shouldNotify = $this->option('notify');
        
        $this->info('Processing expired module trials...');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Get expired trials
        $expired = \App\Models\TenantModuleSubscription::trialExpired()
            ->where('status', 'active')
            ->with(['tenant', 'module'])
            ->get();

        $this->info("Found {$expired->count()} expired trials");

        if ($expired->isEmpty()) {
            $this->info('No expired trials to process.');
            return self::SUCCESS;
        }

        // Show trials that will be processed
        $rows = $expired->map(fn($sub) => [
            $sub->id,
            $sub->tenant->name ?? 'Unknown',
            $sub->module->name ?? $sub->module_key,
            $sub->trial_ends_at->format('Y-m-d'),
            $sub->tenant->owner?->email ?? 'No owner',
        ]);

        $this->table(
            ['ID', 'Tenant', 'Module', 'Trial Ended', 'Owner Email'],
            $rows
        );

        if ($isDryRun) {
            $this->info('\nDry run complete. No changes made.');
            return self::SUCCESS;
        }

        if (!$this->confirm('Do you want to process these trials?')) {
            $this->info('Cancelled.');
            return self::SUCCESS;
        }

        // Process trials
        $results = $this->billingService->processExpiredTrials();

        $this->info("\nResults:");
        $this->table(
            ['Action', 'Count'],
            [
                ['Processed', $results['processed']],
                ['Converted to Paid', $results['converted']],
                ['Suspended', $results['suspended']],
            ]
        );

        // Send notifications if requested
        if ($shouldNotify && $results['suspended'] > 0) {
            $this->info("\nSending suspension notifications...");
            // Notification logic would go here
        }

        $this->info('Done!');
        return self::SUCCESS;
    }
}
