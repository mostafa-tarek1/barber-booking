<?php

namespace App\Modules\Rooms\Infrastructure\Persistence\Models\RoomAdditional;

use Illuminate\Database\Eloquent\Model;

class RoomAdditionalTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = ['title'];
    protected $table = 'room_additional_translations';

}
