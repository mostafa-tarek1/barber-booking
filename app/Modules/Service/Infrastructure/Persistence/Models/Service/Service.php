<?php

namespace App\Modules\Service\Infrastructure\Persistence\Models\Service;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model implements TranslatableContract
{
    use Translatable;
    use HasFactory;

    public $translatedAttributes = ['name', 'description'];

    protected $table = 'services';
    protected $fillable = ['price', 'duration', 'image', 'is_active'];

    /**
     * The bookings that belong to the service.
     */
    // public function bookings(): BelongsToMany
    // {
    //     return $this->belongsToMany(
    //         Booking::class,
    //         'booking_service',
    //         'service_id',
    //         'booking_id'
    //     )->withPivot(['quantity', 'price'])->withTimestamps();
    // }
}
