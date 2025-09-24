<?php

namespace App\Modules\Service\Infrastructure\Persistence\Models\Service;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
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

    public function imageLink(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->image && Storage::disk('public')->exists($this->image)) {
                    return asset('storage/' . $this->image);
                }
                return "";
            }
        );
    }

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
