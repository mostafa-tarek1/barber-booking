<?php

namespace App\Modules\Booking\Infrastructure\Persistence\Models\BookingRooms;

use App\Modules\Booking\Domain\Enums\BookingRoomsStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Rooms\Infrastructure\Persistence\Models\Room\Room;
use App\Modules\Rooms\Infrastructure\Persistence\Models\RoomAdditional\RoomAdditional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class BookingRooms extends Model
{
    use HasFactory;
    protected $table = 'booking_rooms';
    protected $fillable = [
        'booking_id',
        'room_id',
        'room_additional_id',
        'status',
    ];

        protected $casts = [
        'status' => BookingRoomsStatusEnum::class,
    ];
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
    public function roomAdditional()
    {
        return $this->belongsTo(RoomAdditional::class);
    }
}
