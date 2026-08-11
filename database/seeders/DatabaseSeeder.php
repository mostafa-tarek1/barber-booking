<?php

namespace Database\Seeders;

use App\Modules\Auth\Infrastructure\DataBase\Seeders\CashierSeeder;
use App\Modules\Auth\Infrastructure\DataBase\Seeders\SuperAdminSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SuperAdminSeeder::class,
            CashierSeeder::class,
            ServiceCatalogSeeder::class,
            BookingReviewSeeder::class,
        ]);
    }
}
