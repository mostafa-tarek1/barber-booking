<?php

namespace App\Modules\Auth\Infrastructure\DataBase\Seeders;

use App\Modules\Auth\Http\Enums\EmployeeTypeEnum;
use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use Illuminate\Database\Seeder;

class CashierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Employee::query()->updateOrCreate(
            ['email' => 'cashier@barbers.com'],
            [
                'first_name' => 'Cashier',
                'last_name' => 'Account',
                'phone' => '01000000001',
                'password' => '123123123',
                'employee_type' => EmployeeTypeEnum::EMPLOYEE->value,
                'status' => 'active',
                'is_blocked' => false,
            ],
        );
    }
}
