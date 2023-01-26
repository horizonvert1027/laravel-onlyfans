<?php

namespace App\Console;

use App\Jobs\DeleteMedia;
use App\Jobs\RebillWallet;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        $schedule->command('queue:work --tries=3 --timeout=8600')
        ->cron('* * * * *')
        ->withoutOverlapping();

        $schedule->command('cache:clear')
        ->weekly()
        ->withoutOverlapping();

        $schedule->job(new DeleteMedia)->hourly();

        $schedule->job(new RebillWallet)->everyMinute();
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
