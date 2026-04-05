<?php

namespace App\Events\Modules;

use App\Models\TenantModuleSubscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when module billing fails.
 */
class ModuleBillingFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public TenantModuleSubscription $subscription,
        public string $error
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tenant.' . $this->subscription->tenant_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'module_key' => $this->subscription->module_key,
            'error' => $this->error,
            'suspension_date' => now()->addDays(3)->toIso8601String(), // Grace period
            'failed_at' => now()->toIso8601String(),
        ];
    }
}
