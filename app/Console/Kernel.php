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
        // $schedule->command('inspire')->hourly();
        $schedule->command("app:automatic-schedule")->everyMinute()->withoutOverlapping();
        $schedule->command("app:send-pledges-sms")->everyMinute()->withoutOverlapping();
        $schedule->command("app:send-birthday-sms")->dailyAt('08:00')->withoutOverlapping();
        $schedule->command("app:prayer-follow-up")->dailyAt('10:00')->withoutOverlapping();
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
