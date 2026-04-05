<?php

namespace App\Jobs;

use App\DTOs\ModuleInstallRequest;
use App\Models\TenantModuleSubscription;
use App\Services\Modules\ModuleInstaller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Background job for module installation.
 * 
 * Handles long-running installation processes asynchronously,
 * with progress tracking and error handling.
 */
class ModuleInstallationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout;

    /**
     * The subscription being processed.
     */
    public TenantModuleSubscription $subscription;

    /**
     * The installation request.
     */
    public ModuleInstallRequest $request;

    /**
     * Create a new job instance.
     */
    public function __construct(
        TenantModuleSubscription $subscription,
        ModuleInstallRequest $request
    ) {
        $this->subscription = $subscription;
        $this->request = $request;
        $this->tries = config('modules.installation.retry_attempts', 3);
        $this->timeout = config('modules.installation.timeout', 300);
        
        // Set the queue for this job
        $this->onQueue(config('modules.installation.queue', 'default'));
    }

    /**
     * Execute the job.
     */
    public function handle(ModuleInstaller $installer): void
    {
        // Set tenant context for the job
        $originalTenantId = config('app.tenant_id');
        config(['app.tenant_id' => $this->request->tenant->id]);

        try {
            Log::info('Starting module installation job', [
                'subscription_id' => $this->subscription->id,
                'module' => $this->request->moduleKey,
                'tenant' => $this->request->tenant->id,
            ]);

            // Update status to installing
            $this->subscription->update(['status' => 'installing']);

            // Perform installation
            $result = $installer->install($this->request);

            if ($result->success) {
                Log::info('Module installation job completed successfully', [
                    'subscription_id' => $this->subscription->id,
                    'module' => $this->request->moduleKey,
                ]);
            } else {
                Log::error('Module installation job failed', [
                    'subscription_id' => $this->subscription->id,
                    'module' => $this->request->moduleKey,
                    'error' => $result->error,
                ]);
                
                // Will trigger job failure
                throw new \RuntimeException($result->error ?? 'Installation failed');
            }

        } catch (\Throwable $e) {
            Log::error('Module installation job exception', [
                'subscription_id' => $this->subscription->id,
                'module' => $this->request->moduleKey,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Update subscription status
            $this->subscription->update([
                'status' => 'failed',
                'installation_error' => $e->getMessage(),
            ]);

            throw $e;
        } finally {
            // Restore original tenant context
            config(['app.tenant_id' => $originalTenantId]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Module installation job failed permanently', [
            'subscription_id' => $this->subscription->id,
            'module' => $this->request->moduleKey,
            'tenant' => $this->request->tenant->id,
            'error' => $exception->getMessage(),
        ]);

        // Ensure subscription is marked as failed
        $this->subscription->update([
            'status' => 'failed',
            'installation_error' => $exception->getMessage(),
        ]);

        // Dispatch failure event for notifications
        $module = app(\App\Repositories\Contracts\ModuleRepositoryInterface::class)
            ->findByKey($this->request->moduleKey);

        event(new \App\Events\Modules\ModuleInstallationFailed(
            $module,
            $this->request->tenant,
            $this->subscription,
            $exception
        ));
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(config('modules.installation.retry_after', 60));
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'module-installation',
            'tenant:' . $this->request->tenant->id,
            'module:' . $this->request->moduleKey,
        ];
    }
}
