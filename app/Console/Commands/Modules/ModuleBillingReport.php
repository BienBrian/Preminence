<?php

namespace App\Console\Commands\Modules;

use App\Models\ModuleInvoiceItem;
use App\Services\Modules\BillingScheduleService;
use Illuminate\Console\Command;

class ModuleBillingReport extends Command
{
    protected $signature = 'modules:billing-report
                            {--period=30 : Number of days to report on}
                            {--tenant= : Specific tenant ID}
                            {--format=table : Output format (table, json, csv)}';

    protected $description = 'Generate billing report for modules';

    private BillingScheduleService $scheduleService;

    public function __construct(BillingScheduleService $scheduleService)
    {
        parent::__construct();
        $this->scheduleService = $scheduleService;
    }

    public function handle(): int
    {
        $period = (int) $this->option('period');
        $format = $this->option('format');
        $tenantId = $this->option('tenant');
        
        $startDate = now()->subDays($period);
        $endDate = now();

        $this->info("Module Billing Report: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

        // Query invoice items
        $query = ModuleInvoiceItem::whereBetween('created_at', [$startDate, $endDate]);
        
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $items = $query->get();

        // Calculate statistics
        $stats = [
            'total_revenue' => $items->where('status', 'paid')->sum('total_amount'),
            'pending_revenue' => $items->where('status', 'pending')->sum('total_amount'),
            'failed_revenue' => $items->where('status', 'failed')->sum('total_amount'),
            'refunded_amount' => $items->where('status', 'refunded')->sum('total_amount'),
            'total_items' => $items->count(),
            'by_type' => $items->groupBy('type')->map->sum('total_amount'),
            'by_module' => $items->groupBy('module_key')->map->sum('total_amount'),
            'by_status' => $items->groupBy('status')->map->count(),
        ];

        // Output based on format
        switch ($format) {
            case 'json':
                $this->line(json_encode($stats, JSON_PRETTY_PRINT));
                break;
                
            case 'csv':
                $this->outputCsv($items);
                break;
                
            default:
                $this->outputTable($stats, $items);
        }

        return self::SUCCESS;
    }

    private function outputTable(array $stats, $items): void
    {
        $this->info("\n=== Revenue Summary ===");
        $this->table(
            ['Metric', 'Amount (KES)'],
            [
                ['Total Revenue', number_format($stats['total_revenue'], 2)],
                ['Pending Revenue', number_format($stats['pending_revenue'], 2)],
                ['Failed Revenue', number_format($stats['failed_revenue'], 2)],
                ['Refunded', number_format($stats['refunded_amount'], 2)],
            ]
        );

        $this->info("\n=== By Type ===");
        $typeRows = $stats['by_type']->map(fn($amount, $type) => [
            ucfirst(str_replace('_', ' ', $type)),
            number_format($amount, 2),
        ])->values();
        $this->table(['Type', 'Amount (KES)'], $typeRows);

        $this->info("\n=== By Status ===");
        $statusRows = $stats['by_status']->map(fn($count, $status) => [
            ucfirst($status),
            $count,
        ])->values();
        $this->table(['Status', 'Count'], $statusRows);

        $this->info("\n=== Top Modules ===");
        $moduleRows = $stats['by_module']
            ->sortDesc()
            ->take(10)
            ->map(fn($amount, $module) => [
                $module,
                number_format($amount, 2),
            ])->values();
        $this->table(['Module', 'Revenue (KES)'], $moduleRows);

        $this->info("\nTotal Items: {$stats['total_items']}");
    }

    private function outputCsv($items): void
    {
        // Header
        $this->line('ID,Date,Tenant,Module,Type,Description,Amount,Status');
        
        // Data
        foreach ($items as $item) {
            $this->line(sprintf(
                '%d,%s,%s,%s,%s,"%s",%.2f,%s',
                $item->id,
                $item->created_at->format('Y-m-d'),
                $item->tenant->name ?? 'Unknown',
                $item->module_key,
                $item->type,
                str_replace('"', '""', $item->description),
                $item->total_amount,
                $item->status
            ));
        }
    }
}
