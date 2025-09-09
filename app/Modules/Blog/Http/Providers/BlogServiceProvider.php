<?php

namespace App\Modules\Blog\Http\Providers;

use Illuminate\Support\ServiceProvider;

class BlogServiceProvider extends ServiceProvider
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
        $this->loadMigrationsFrom(app_path('Modules/Blog/Infrastructure/DataBase/Migrations'));

        $this->loadRoutesFrom(app_path('Modules/Blog/Http/Routes/Api.php'));
        $this->loadRoutesFrom(app_path('Modules/Blog/Http/Routes/Dashboard.php'));
    }
}
