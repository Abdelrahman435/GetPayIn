<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\ExpireHoldsJob;

class Kernel extends ConsoleKernel
{
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        app(\App\Repositories\HoldRepository::class)->releaseExpiredHolds();
    })->everyMinute();
}

}
