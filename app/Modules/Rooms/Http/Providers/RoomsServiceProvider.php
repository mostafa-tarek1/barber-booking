<?php

namespace App\Modules\Rooms\Http\Providers;


use Illuminate\Support\ServiceProvider;

class RoomsServiceProvider extends ServiceProvider
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
        $this->loadMigrationsFrom(app_path('Modules/Rooms/Infrastructure/DataBase/Migrations'));

        $this->loadRoutesFrom(app_path('Modules/Rooms/Http/Routes/Api.php'));
        $this->loadRoutesFrom(app_path('Modules/Rooms/Http/Routes/Dashboard.php'));
        foreach (glob(app_path('Modules/Rooms/Application/Helpers') . '/*.php') as $filename) {
            require_once $filename;
        }
    }
}
