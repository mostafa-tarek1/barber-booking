<?php

namespace App\Modules\Statistics\Http\Providers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ServiceProvider;

class StatisticsServiceProvider extends ServiceProvider
{
        /**
     * Register services.
     */
    public function register(): void {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
        $this->loadMigrationsFrom(app_path('Modules/Statistics/Infrastructure/DataBase/Migrations'));

        $this->loadRoutesFrom(app_path('Modules/Statistics/Http/Routes/Api.php'));
        $this->loadRoutesFrom(app_path('Modules/Statistics/Http/Routes/Dashboard.php'));

    }
}
