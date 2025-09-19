<?php

namespace App\Modules\Rooms\Infrastructure\Persistence\Models\Room;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Rooms\Infrastructure\Persistence\Models\RoomAdditional\RoomAdditional;
use App\Modules\Rooms\Infrastructure\Persistence\Models\RoomLocation\RoomLocation;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;


class Room extends Model implements TranslatableContract
{
    use Translatable;
    use HasFactory;

    public $translatedAttributes = ['title', 'description'];

    protected $table = 'rooms';
    protected $fillable = ['price', 'created_by', 'updated_by' , 'image',];

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
    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
    public function locations()
    {
        return $this->hasMany(RoomLocation::class);
    }
    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_rooms');
    }
    public function roomAdditionals()
    {
        return $this->hasMany(RoomAdditional::class);
    }
}
