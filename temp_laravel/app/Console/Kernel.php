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
        // Run policy expiry checks every day at 9 AM
        $schedule->command('policies:check-expiries')
                 ->daily()
                 ->at('09:00')
                 ->appendOutputTo(storage_path('logs/schedule.log'));

        // Run cleanup of old notifications every week on Sunday at 2 AM
        $schedule->command('policies:check-expiries --cleanup')
                 ->weekly()
                 ->sundays()
                 ->at('02:00')
                 ->appendOutputTo(storage_path('logs/schedule.log'));
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