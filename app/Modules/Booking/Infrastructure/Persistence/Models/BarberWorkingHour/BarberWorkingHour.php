<?php

namespace App\Modules\Booking\Infrastructure\Persistence\Models\BarberWorkingHour;

use Illuminate\Database\Eloquent\Model;

class BarberWorkingHour extends Model
{
    protected $fillable = ['employee_id', 'weekday', 'from_hour', 'to_hour', 'is_day_off'];

    protected $casts = [
        'is_day_off' => 'boolean',
    ];
}
