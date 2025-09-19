<?php

namespace App\Modules\Rooms\Infrastructure\Persistence\Models\Room;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoomTranslation extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'room_translations';
    protected $fillable = ['title', 'description'];


}
