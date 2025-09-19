<?php

namespace App\Modules\Rooms\Infrastructure\Persistence\Models\RoomLocationMedia;

use App\Modules\Rooms\Domain\Enums\RoomLocationMediaFileTypeEnum;
use App\Modules\Rooms\Domain\Enums\RoomLocationMediaTypeEnum;
use App\Modules\Rooms\Infrastructure\Persistence\Models\RoomLocation\RoomLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;



class RoomLocationMedia extends Model
{
    use HasFactory;
    protected $table = 'room_location_media';
    protected $fillable = [
        'room_location_id',
        'type',
        'file',
        'alt',
        'title',
        'file_type',
        'status',
    ];

    protected $casts = [
        'type' => RoomLocationMediaTypeEnum::class,
        'file_type' => RoomLocationMediaFileTypeEnum::class,
        'status' => 'boolean',
    ];
    public function roomLocation()
    {
        return $this->belongsTo(RoomLocation::class, 'room_location_id');
    }

    public function FileLink(): Attribute
    {
        if (isset($this->file) && Storage::disk('public')->exists($this->file)) {
            return Attribute::make(fn() => asset('storage/' . $this->file));
        } else {
            return Attribute::make(fn() => "");
        }
    }
}
