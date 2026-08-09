<?php

namespace App\Modules\Booking\Infrastructure\Persistence\Models\Invoice;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use App\Modules\Service\Infrastructure\Persistence\Models\Package\Package;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'service_id',
        'package_id',
        'employee_id',
        'description',
        'unit_price',
        'quantity',
        'total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
