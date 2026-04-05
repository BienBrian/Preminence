<?php

namespace App\Events\Modules;

use App\Models\TenantModuleSubscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a module subscription is billed.
 */
class ModuleBilled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public TenantModuleSubscription $subscription,
        public array $invoiceItem
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
            'amount' => $this->invoiceItem['amount'] ?? null,
            'currency' => $this->subscription->currency,
            'period_start' => $this->invoiceItem['period_start'] ?? null,
            'period_end' => $this->invoiceItem['period_end'] ?? null,
            'billed_at' => now()->toIso8601String(),
        ];
    }
}
