<?php

namespace App\Console\Commands;

use App\Services\MpesaContactSyncService;
use Illuminate\Console\Command;

class SyncMpesaContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mpesa:sync-contacts 
                            {--bulk : Rehash all contacts that do not have mpesa_phone records}
                            {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync contact phone numbers with MPESA phone hashes';

    /**
     * Execute the console command.
     */
    public function handle(MpesaContactSyncService $syncService)
    {
        $isBulk = $this->option('bulk');
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        if ($isBulk) {
            $this->info('Starting bulk contact rehash...');
            
            if ($isDryRun) {
                // Just show counts in dry-run mode
                $contactsCount = \DB::table('contacts')
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->count();
                
                $existingHashes = \DB::table('mpesa_phones')->count();
                
                $this->table(['Metric', 'Count'], [
                    ['Total contacts with phones', $contactsCount],
                    ['Existing mpesa_phone records', $existingHashes],
                    ['Potential new records', $contactsCount - $existingHashes],
                ]);
                
                return 0;
            }

            $stats = $syncService->rehashAllContacts();
            
            $this->newLine();
            $this->info('Bulk rehash completed!');
            $this->table(['Metric', 'Count'], [
                ['Contacts processed', $stats['processed']],
                ['New mpesa_phone records created', $stats['created']],
                ['Skipped (already exists)', $stats['skipped']],
                ['Errors', $stats['errors']],
            ]);
        } else {
            $this->info('Use --bulk option to rehash all contacts');
            $this->line('');
            $this->line('This command ensures all contact phone numbers are');
            $this->line('hashed and stored in the mpesa_phones table for');
            $this->line('automatic SMS delivery on future MPESA transactions.');
            $this->line('');
            $this->info('Run with --bulk to process all contacts');
            $this->info('Run with --bulk --dry-run to preview changes');
        }

        return 0;
    }
}
