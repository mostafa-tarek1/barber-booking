<?php

namespace App\Modules\Rooms\Http\Resources\Api;

use App\Modules\Rooms\Http\Resources\Api\RoomLocation\RoomLocationResource;
use App\Modules\Rooms\Http\Resources\Api\RoomAdditional\RoomAdditionalResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class RoomDetailResource extends RoomResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        return array_merge($data, [
            'room_additional' => RoomAdditionalResource::collection($this->roomAdditionals),
            'room_location' => RoomLocationResource::collection($this->locations ?? collect()),
        ]);
    }
}
