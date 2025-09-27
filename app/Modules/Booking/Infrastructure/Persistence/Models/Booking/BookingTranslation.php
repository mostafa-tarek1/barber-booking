<?php

namespace App\Modules\Booking\Infrastructure\Persistence\Models\Booking;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookingTranslation extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'booking_translations';
    protected $fillable = [];
}
