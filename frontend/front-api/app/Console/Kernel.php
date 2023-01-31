<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Helpers\Export;
use App\Helpers\DocCounter;
use App\Helpers\StorageMonitor;
use App\Helpers\DataMonitor;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        $schedule->call(function() {
            $threshold = time() - (12*24*60*60); // files older than 12 days delete
            foreach (glob(storage_path('app/export')."/*") as $file) {
                if (filemtime($file) < $threshold) {
                    unlink($file);
                }
            }
        })->daily();

        $schedule->call(function() {
            $job = new Export();   // run export of data
            $job->exec();
        });

        $schedule->call(function() {
            $dc = new DocCounter;  // count number of records per dataset and store info in database
            $dc->exec();
        })->everyThirtyMinutes();

        $schedule->call(function() {
            $sm = new StorageMonitor;  // calculate how much storage every user is using and report this
            $sm->exec();
        })->daily();
/*
        $schedule->call(function() {
            $dm = new DataMonitor;  // see if there is an anomoulous number of items for a publisher
            $dm->exec();
        })->dailyAt('12:00');
*/



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
