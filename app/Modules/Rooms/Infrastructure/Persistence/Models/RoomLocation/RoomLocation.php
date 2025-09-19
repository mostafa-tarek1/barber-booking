<?php

namespace App\Modules\Rooms\Infrastructure\Persistence\Models\RoomLocation;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Rooms\Infrastructure\Persistence\Models\Room\Room;
use App\Modules\Rooms\Infrastructure\Persistence\Models\RoomLocationMedia\RoomLocationMedia;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RoomLocation extends Model implements TranslatableContract
{
    use Translatable;

    protected $fillable = [
        'price',
        'room_id',
        'created_by',
    ];

    public $translatedAttributes = [
        'title',
        'description',
    ];

    protected $table = 'room_locations';


    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
    public function medias()
    {
        return $this->hasMany(RoomLocationMedia::class, 'room_location_id');
    }
}
