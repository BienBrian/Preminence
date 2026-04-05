<?php

namespace App\Providers;

use App\Repositories\CachedModuleRepository;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use App\Repositories\ModuleRepository;
use Illuminate\Support\ServiceProvider;

class ModuleMarketplaceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interface to implementation with caching decorator
        $this->app->singleton(ModuleRepositoryInterface::class, function ($app) {
            $baseRepository = new ModuleRepository();
            
            // Wrap with caching decorator
            return new CachedModuleRepository(
                $baseRepository,
                config('modules.cache_ttl_minutes', 5)
            );
        });

        // Also bind concrete class for direct access if needed
        $this->app->singleton(ModuleRepository::class, function ($app) {
            return new ModuleRepository();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register event listeners
        $this->registerEventListeners();

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    /**
     * Register event listeners for module events.
     */
    protected function registerEventListeners(): void
    {
        // Module installation events
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Modules\ModuleInstalled::class,
            function ($event) {
                // Invalidate cache when module is installed
                app(ModuleRepositoryInterface::class)->invalidateTenant($event->tenant->id);
                
                // Log to platform audit
                \App\Models\PlatformAuditLog::record(
                    'module.installed',
                    [
                        'module_key' => $event->module->key,
                        'tenant_id' => $event->tenant->id,
                        'billing_type' => $event->subscription->billing_type,
                        'installed_by' => $event->installedBy?->id,
                    ],
                    $event->tenant->id,
                    $event->installedBy?->id
                );
            }
        );

        // Module uninstallation events
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Modules\ModuleUninstalled::class,
            function ($event) {
                app(ModuleRepositoryInterface::class)->invalidateTenant($event->tenant->id);
                
                \App\Models\PlatformAuditLog::record(
                    'module.uninstalled',
                    [
                        'module_key' => $event->module->key,
                        'tenant_id' => $event->tenant->id,
                        'reason' => $event->reason,
                    ],
                    $event->tenant->id
                );
            }
        );

        // Module billing events
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Modules\ModuleBillingFailed::class,
            function ($event) {
                // Notify tenant admin about billing failure
                $event->subscription->tenant->owner?->notify(
                    new \App\Notifications\ModuleBillingFailed($event->subscription)
                );
            }
        );
    }

    /**
     * Register console commands.
     */
    protected function registerCommands(): void
    {
        // Commands will be registered here when created
        // $this->commands([
        //     \App\Console\Commands\ModuleHealthCheck::class,
        // ]);
    }
}
