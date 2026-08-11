<?php

namespace App\Modules\Finance\Http\Providers;

use Illuminate\Support\ServiceProvider;

class FinanceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(app_path('Modules/Finance/Infrastructure/DataBase/Migrations'));
        $this->loadRoutesFrom(app_path('Modules/Finance/Http/Routes/Dashboard.php'));
    }
}
