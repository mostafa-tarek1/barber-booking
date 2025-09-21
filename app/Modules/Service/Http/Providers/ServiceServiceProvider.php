<?php

namespace App\Modules\Service\Http\Providers;

use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;

use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
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
        $this->loadMigrationsFrom(app_path('Modules/Service/Infrastructure/DataBase/Migrations'));

        $this->loadRoutesFrom(app_path('Modules/Service/Http/Routes/Api.php'));
        $this->loadRoutesFrom(app_path('Modules/Service/Http/Routes/Dashboard.php'));
        foreach (glob(app_path('Modules/Service/Application/Helpers') . '/*.php') as $filename) {
            require_once $filename;
        }
    }
}
