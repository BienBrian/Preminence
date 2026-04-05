<?php

namespace App\Events\Modules;

use App\Models\Module;
use App\Models\Tenant;
use App\Models\TenantModuleSubscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when module installation fails.
 */
class ModuleInstallationFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Module $module,
        public Tenant $tenant,
        public TenantModuleSubscription $subscription,
        public \Throwable $exception
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tenant.' . $this->tenant->id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'module_key' => $this->module->key,
            'module_name' => $this->module->name,
            'subscription_id' => $this->subscription->id,
            'error' => $this->exception->getMessage(),
            'error_code' => $this->exception instanceof \App\Exceptions\Modules\ModuleInstallationException
                ? $this->exception->getErrorCode()
                : 'UNKNOWN_ERROR',
            'failed_at' => now()->toIso8601String(),
        ];
    }
}
