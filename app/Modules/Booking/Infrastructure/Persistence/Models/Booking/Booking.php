<?php

namespace App\Modules\Booking\Infrastructure\Persistence\Models\Booking;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use App\Modules\Booking\Infrastructure\Persistence\Models\Invoice\Invoice;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Service\Infrastructure\Persistence\Models\Package\Package;

class Booking extends Model implements TranslatableContract
{
    use Translatable;
    use HasFactory;
    use SoftDeletes;

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
        'expires_at',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'date' => 'datetime',
        'from_hour' => 'string',
        'to_hour' => 'string',
        'expires_at' => 'datetime',
    ];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'booking_service', 'booking_id', 'service_id')
            ->withPivot([
                'employee_id',
                'package_id',
                'name_snapshot',
                'price_snapshot',
                'duration_snapshot',
                'quantity',
                'created_at',
                'updated_at',
            ])
            ->select('services.*'); // Include all columns from services table
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'booking_package')
            ->withPivot(['name_snapshot', 'price_snapshot', 'duration_snapshot'])
            ->withTimestamps();
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

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
