<?php

namespace App\Modules\Auth\Infrastructure\Persistence\Models\Attendance;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    protected $fillable = [
        'latitude',
        'longitude',
        'allowed_radius_meters',
        'updated_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'allowed_radius_meters' => 'integer',
    ];

    public function updatedBy()
    {
        return $this->belongsTo(Employee::class, 'updated_by');
    }
}
