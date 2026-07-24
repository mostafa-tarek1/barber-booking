<?php

namespace App\Modules\Auth\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Resources\Api\BarberResource;
use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;

class EmployeeController extends Controller
{
    public function barbers()
    {
        $barbers = Employee::query()
            ->where('employee_type', 'barber')
            ->where('status', 'active')
            ->where('is_active', true)
            ->where('is_blocked', false)
            ->orderBy('first_name')
            ->get();

        return response()->json([
            'status' => 200,
            'message' => 'Barbers fetched successfully',
            'data' => BarberResource::collection($barbers),
        ]);
    }
}
