<?php
namespace App\Modules\Rooms\Infrastructure\Persistence\Models\RoomAdditional;

use App\Modules\Booking\Infrastructure\Persistence\Models\BookingRooms\BookingRooms;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Translatable;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class RoomAdditional extends Model implements TranslatableContract
{
    use Translatable;

    protected $fillable = [
        'room_id',
        'parent_id',
        'image',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
    protected $table = 'room_additionals';

    public $translatedAttributes = ['title'];

    public function parent()
    {
        return $this->belongsTo(RoomAdditional::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(RoomAdditional::class, 'parent_id');
    }

    public function bookings()
    {
        return $this->belongsToMany(BookingRooms::class, 'booking_rooms', 'room_additional_id', 'booking_id');
    }
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

    public function bookingRooms()
    {
        return $this->hasMany(BookingRooms::class);
    }
}
