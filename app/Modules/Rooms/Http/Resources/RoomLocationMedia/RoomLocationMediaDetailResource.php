<?php

namespace App\Modules\Rooms\Http\Resources\RoomLocationMedia;

use Illuminate\Http\Request;
class RoomLocationMediaDetailResource extends RoomLocationMediaResource
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
