<?php

namespace App\Modules\Rooms\Http\Resources\RoomAdditional;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomAdditionalResource extends GeneralRoomAdditionalResource
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
            'room_id' => $this->room_id,
            'parent_id' => $this->parent_id,
            'image' => $this->image_link,
            'children' => RoomAdditionalResource::collection($this->whenLoaded('children')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ]);
    }
}
