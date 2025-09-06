<?php

namespace App\Modules\Auth\Infrastructure\Persistence\Models\Admin\EmployeeDevice;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'device_id',
        'device_type',
        'device_token',
        'device_os',
        'device_os_version',
        'device_model',
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
