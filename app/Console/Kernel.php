<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Existing app schedules
        $schedule->command("app:automatic-schedule")->everyMinute()->withoutOverlapping();
        $schedule->command("app:send-pledges-sms")->everyMinute()->withoutOverlapping();
        $schedule->command("app:send-birthday-sms")->dailyAt('08:00')->withoutOverlapping();
        $schedule->command("app:prayer-follow-up")->dailyAt('10:00')->withoutOverlapping();
        $schedule->command("app:retry-pending-sms")->everyFiveMinutes()->withoutOverlapping();

        // Horizon metrics snapshots (needed for Horizon dashboard charts)
        $schedule->command('horizon:snapshot')->everyFiveMinutes();

        // ─── Pisti SaaS Platform — Module Billing Schedule ────────────────────
        // Daily at 8 AM: Generate invoices for due subscriptions
        $schedule->command('modules:generate-invoices')->dailyAt('08:00')->withoutOverlapping();
        
        // Daily at 9 AM: Process expired trials
        $schedule->command('modules:process-trials')->dailyAt('09:00')->withoutOverlapping();
        
        // Daily at 10 AM: Process overdue payments
        $schedule->command('modules:process-overdue --suspend')->dailyAt('10:00')->withoutOverlapping();
        
        // Weekly on Mondays at 6 AM: Generate billing report
        $schedule->command('modules:billing-report')->weeklyOn(1, '06:00')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
