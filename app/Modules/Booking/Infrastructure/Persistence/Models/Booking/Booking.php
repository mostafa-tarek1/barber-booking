<?php

namespace App\Modules\Booking\Infrastructure\Persistence\Models\Booking;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model implements TranslatableContract
{
    use Translatable;
    use HasFactory;

    public $translatedAttributes = ['notes', 'locale'];

    protected $table = 'bookings';
    protected $fillable = [
        'name',
        'phone',
        'email',
        'date',
        'from_hour',
        'to_hour',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'date' => 'datetime',
        'from_hour' => 'string',
        'to_hour' => 'string',
    ];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'booking_service', 'booking_id', 'service_id')
            ->withPivot(['employee_id', 'created_at', 'updated_at'])
            ->select('services.*'); // Include all columns from services table
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Employee::class, 'updated_by');
    }
    
    public function employee()
    {
        // Get the employee from the first service in the booking
        return $this->belongsToMany(Employee::class, 'booking_service', 'booking_id', 'employee_id')
            ->withPivot(['service_id', 'created_at', 'updated_at'])
            ->limit(1)
            ->withDefault();
    }
}
