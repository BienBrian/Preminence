<?php

namespace App\Console\Commands\Modules;

use App\Services\Modules\ModuleInvoiceService;
use App\Services\Modules\ModuleBillingService;
use Illuminate\Console\Command;

class GenerateModuleInvoices extends Command
{
    protected $signature = 'modules:generate-invoices
                            {--date= : Date to generate invoices for (Y-m-d)}
                            {--tenant= : Specific tenant ID}
                            {--dry-run : Show what would be generated without creating}';

    protected $description = 'Generate invoice items for due module subscriptions';

    private ModuleInvoiceService $invoiceService;
    private ModuleBillingService $billingService;

    public function __construct(
        ModuleInvoiceService $invoiceService,
        ModuleBillingService $billingService
    ) {
        parent::__construct();
        $this->invoiceService = $invoiceService;
        $this->billingService = $billingService;
    }

    public function handle(): int
    {
        $date = $this->option('date') ? \Carbon\Carbon::parse($this->option('date')) : now();
        $isDryRun = $this->option('dry-run');
        
        $this->info("Generating module invoices for: {$date->format('Y-m-d')}");
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No invoices will be created');
        }

        if ($tenantId = $this->option('tenant')) {
            $this->info("Processing tenant: {$tenantId}");
        }

        // Generate invoice items
        $stats = $this->invoiceService->generateDueInvoiceItems($isDryRun ? null : $date);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Processed', $stats['processed']],
                ['Created', $stats['created']],
                ['Skipped', $stats['skipped']],
                ['Errors', count($stats['errors'])],
            ]
        );

        if (!empty($stats['errors'])) {
            $this->error("\nErrors encountered:");
            foreach ($stats['errors'] as $error) {
                $this->error("  Subscription {$error['subscription_id']}: {$error['error']}");
            }
            return self::FAILURE;
        }

        // Process payments if not dry run
        if (!$isDryRun && $stats['created'] > 0) {
            $this->info("\nProcessing payments...");
            $billingStats = $this->billingService->processDueBillings();
            
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Processed', $billingStats['processed']],
                    ['Successful', $billingStats['successful']],
                    ['Failed', $billingStats['failed']],
                ]
            );
        }

        $this->info("\nDone!");
        return self::SUCCESS;
    }
}
