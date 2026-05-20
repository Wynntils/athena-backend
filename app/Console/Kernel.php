<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new \App\Jobs\Cache\RefreshServerListCache)->everyThirtySeconds()->onOneServer();
        $schedule->job(new \App\Jobs\Cache\RefreshTerritoryListCache)->everyFifteenSeconds()->onOneServer();
        $schedule->job(new \App\Jobs\Cache\RefreshLeaderboardCache)->everyTenMinutes()->onOneServer();
        $schedule->job(new \App\Jobs\Cache\RefreshGuildListCache)->hourly()->onOneServer();
        $schedule->job(new \App\Jobs\Cache\RefreshItemWeightsCache)->hourly()->onOneServer();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
