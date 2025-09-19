<?php

namespace App\Modules\Booking\Http\Providers;


use Illuminate\Support\ServiceProvider;

class BookingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
        $this->loadMigrationsFrom(app_path('Modules/Booking/Infrastructure/DataBase/Migrations'));

        $this->loadRoutesFrom(app_path('Modules/Booking/Http/Routes/Api.php'));
        $this->loadRoutesFrom(app_path('Modules/Booking/Http/Routes/Dashboard.php'));
        foreach (glob(app_path('Modules/Booking/Application/Helpers') . '/*.php') as $filename) {
            require_once $filename;
        }
    }
}
