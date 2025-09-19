<?php

namespace App\Modules\Booking\Infrastructure\Persistence\Models\Booking;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\BookingRooms\BookingRooms;
use App\Modules\Rooms\Infrastructure\Persistence\Models\Room\Room;
use App\Modules\Rooms\Infrastructure\Persistence\Models\RoomAdditional\RoomAdditional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

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
        'updated_by',
    ];
    protected $casts = [
        'status' => BookingStatusEnum::class,
        'date' => 'date',
    ];
    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
    public function updater()
    {
        return $this->belongsTo(Employee::class, 'updated_by');
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'booking_rooms');
    }
    public function bookingRooms()
    {
        return $this->hasMany(BookingRooms::class);
    }
    public function room_additional()
    {
        return $this->belongsToMany(RoomAdditional::class, 'booking_rooms', 'booking_id', 'room_additional_id');
    }
    public function room_additionals()
    {
        return $this->belongsToMany(RoomAdditional::class, 'booking_rooms', 'booking_id', 'room_additional_id');
    }
}
