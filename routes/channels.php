<?php

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('employees.{employeeId}.notifications', function (Employee $employee, int $employeeId) {
    return $employee->id === $employeeId;
});
