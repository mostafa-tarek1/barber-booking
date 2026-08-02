<?php

namespace App\Modules\Booking\Infrastructure\Persistence\Models\SalonCustomer;

use App\Modules\Booking\Infrastructure\Persistence\Models\Invoice\Invoice;
use Illuminate\Database\Eloquent\Model;

class SalonCustomer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'normalized_phone',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
