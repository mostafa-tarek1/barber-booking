<?php

namespace App\Modules\Auth\Infrastructure\DataBase\Seeders;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Employee::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@locations.com',
            'password' => '123123123',
            'phone' => '123123123',
            'employee_type' => 'super_admin',
            'is_blocked' => false,
        ]);
    }
}
