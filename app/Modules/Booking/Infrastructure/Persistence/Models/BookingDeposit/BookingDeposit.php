<?php

namespace App\Modules\Booking\Infrastructure\Persistence\Models\BookingDeposit;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use Illuminate\Database\Eloquent\Model;

class BookingDeposit extends Model
{
    protected $fillable = [
        'booking_id',
        'collected_by',
        'payment_method',
        'amount',
        'collected_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'collected_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function collector()
    {
        return $this->belongsTo(Employee::class, 'collected_by');
    }
}
