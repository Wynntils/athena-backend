<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         $schedule->command('patreon:update')->daily();

        foreach (\App\Managers\CacheManager::$cacheTable as $cacheName => $cacheClass) {
            $instance = new $cacheClass;

            if ($instance instanceof \App\Http\Libraries\Requests\Cache\CacheContract) {
                $rateInSeconds = $instance->refreshRate();
                $rateInMinutes = max(1, (int) ceil($rateInSeconds / 60)); // Minimum 1-minute interval

                $schedule->job(new \App\Jobs\GenerateCacheJob($cacheName))
                    ->cron("*/{$rateInMinutes} * * * *") // Use cron syntax for variable intervals
                    ;
            }
        }

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
