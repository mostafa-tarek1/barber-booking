<?php

namespace App\Modules\Auth\Infrastructure\Persistence\Models\Attendance;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'employee_id',
        'attendance_date',
        'checked_in_at',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_distance_meters',
        'check_in_accuracy_meters',
        'checked_out_at',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_distance_meters',
        'check_out_accuracy_meters',
    ];

    protected $casts = [
        'attendance_date' => 'date:Y-m-d',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'check_in_latitude' => 'decimal:7',
        'check_in_longitude' => 'decimal:7',
        'check_in_distance_meters' => 'decimal:2',
        'check_in_accuracy_meters' => 'decimal:2',
        'check_out_latitude' => 'decimal:7',
        'check_out_longitude' => 'decimal:7',
        'check_out_distance_meters' => 'decimal:2',
        'check_out_accuracy_meters' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
