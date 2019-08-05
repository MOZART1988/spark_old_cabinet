<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Http\Controllers\amoController;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Send1C::class,
        Commands\Send1CTest::class,
        Commands\price::class,
        Commands\addtask::class,
        Commands\LeadComplete::class,
        Commands\LeadShipped::class,
        Commands\pickUp::class,
        Commands\delivery::class,
        Commands\pickUpAndDeliver::class,
        Commands\getStatistics::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {amoController::setWaybills();})
                 ->cron('0 0-23/3 * * *');
        $schedule->call(function () {amoController::getStatistics();})
                 ->hourly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
