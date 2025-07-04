<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule; // ✅ correct

class ScheduleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // ⏰ Schedule your command
            $schedule->command('rents:generate-monthly')->everyMinute(); // 🧪 testing

        });
    }
}
