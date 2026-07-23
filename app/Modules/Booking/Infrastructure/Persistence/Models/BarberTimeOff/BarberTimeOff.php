<?php

namespace App\Modules\Booking\Infrastructure\Persistence\Models\BarberTimeOff;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use Illuminate\Database\Eloquent\Model;

class BarberTimeOff extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'from_hour',
        'to_hour',
        'is_day_off',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'is_day_off' => 'boolean',
    ];

    public function barber()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
