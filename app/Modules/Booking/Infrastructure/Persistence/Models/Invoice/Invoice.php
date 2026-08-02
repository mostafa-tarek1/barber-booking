<?php

namespace App\Modules\Booking\Infrastructure\Persistence\Models\Invoice;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Booking\Infrastructure\Persistence\Models\SalonCustomer\SalonCustomer;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'booking_id',
        'salon_customer_id',
        'customer_name',
        'customer_phone',
        'cashier_id',
        'barber_id',
        'subtotal',
        'discount',
        'total',
        'payment_method',
        'status',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function salonCustomer()
    {
        return $this->belongsTo(SalonCustomer::class);
    }

    public function cashier()
    {
        return $this->belongsTo(Employee::class, 'cashier_id');
    }

    public function barber()
    {
        return $this->belongsTo(Employee::class, 'barber_id');
    }
}
