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

        // ─── Pisti SaaS Platform — Added in Phase 7 ───────────────────────────
        // $schedule->command('saas:check-expiring-trials')->dailyAt('09:00')->withoutOverlapping();
        // $schedule->command('saas:check-overdue-subscriptions')->dailyAt('09:15')->withoutOverlapping();
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
