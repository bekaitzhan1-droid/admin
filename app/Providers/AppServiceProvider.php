<?php

namespace App\Providers;

use App\Models\Confirmation;
use App\Models\Driver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Driver::observe(\App\Observers\DriverObserver::class);
        Confirmation::observe(\App\Observers\ConfirmationObserver::class);
    }
}
