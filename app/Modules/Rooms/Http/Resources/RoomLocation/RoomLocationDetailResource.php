<?php

namespace App\Modules\Rooms\Http\Resources\RoomLocation;

use Illuminate\Http\Request;
class RoomLocationDetailResource extends RoomLocationResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        return $data;
    }
}
