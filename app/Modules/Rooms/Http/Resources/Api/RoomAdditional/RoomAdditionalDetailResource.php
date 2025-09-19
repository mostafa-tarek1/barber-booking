<?php

namespace App\Modules\Rooms\Http\Resources\Api\RoomAdditional;

use App\Modules\Rooms\Http\Resources\RoomAdditional\RoomAdditionalResource;
use Illuminate\Http\Request;
class RoomAdditionalDetailResource extends RoomAdditionalResource
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
