<?php

namespace App\Modules\Rooms\Infrastructure\Persistence\Models\RoomLocation;

use Illuminate\Database\Eloquent\Model;

class RoomLocationTranslation extends Model
{
    public $timestamps = true;
    protected $table = 'room_location_translations';

    protected $fillable = [
        'title',
        'description',
    ];
}
