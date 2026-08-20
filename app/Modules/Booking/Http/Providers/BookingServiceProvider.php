<?php

namespace App\Modules\Booking\Http\Providers;

use App\Modules\Booking\Application\Commands\SendWhatsAppBookingReminders;
use Illuminate\Support\ServiceProvider;

class BookingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Modules\Booking\Domain\Repositories\BookingRepositoryInterface::class,
            \App\Modules\Booking\Infrastructure\Persistence\Repositories\Booking\BookingRepository::class
        );

        $this->app->when(\App\Modules\Booking\Infrastructure\Persistence\Repositories\Booking\BookingRepository::class)
            ->needs(\App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking::class)
            ->give(function () {
                return new \App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking();
            });
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

        $this->commands([SendWhatsAppBookingReminders::class]);
    }
}
