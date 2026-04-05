<?php

namespace App\Notifications;

use App\Models\TenantModuleSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ModuleBillingFailed extends Notification implements ShouldQueue
{
    use Queueable;

    public TenantModuleSubscription $subscription;

    /**
     * Create a new notification instance.
     */
    public function __construct(TenantModuleSubscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $module = $this->subscription->module;
        
        return (new MailMessage)
            ->subject('Payment Failed for ' . ($module?->name ?? $this->subscription->module_key))
            ->line('We were unable to process your payment for the following module:')
            ->line($module?->name ?? $this->subscription->module_key)
            ->line('Amount: ' . $this->subscription->currency . ' ' . number_format($this->subscription->price ?? 0, 2))
            ->line('Your module will be suspended in 3 days if payment is not resolved.')
            ->action('Update Payment Method', url('/billing'))
            ->line('Please update your payment information to avoid service interruption.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'module_key' => $this->subscription->module_key,
            'module_name' => $this->subscription->module?->name,
            'amount' => $this->subscription->price,
            'currency' => $this->subscription->currency,
            'suspension_date' => now()->addDays(3)->toDateString(),
        ];
    }
}
