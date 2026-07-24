<?php

namespace App\Modules\Booking\Infrastructure\Persistence\Models\BookingService;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BookingService extends Pivot
{
    protected $table = 'booking_service';

    protected $fillable = [
        'booking_id',
        'service_id',
        'package_id',
        'employee_id',
        'name_snapshot',
        'price_snapshot',
        'duration_snapshot',
        'quantity',
    ];

    public $timestamps = true;

    /**
     * علاقة مع الحجز
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    /**
     * علاقة مع الخدمة
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * علاقة مع الموظف (الحلاق)
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
