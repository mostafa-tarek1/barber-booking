<?php

namespace App\Modules\Rooms\Http\Resources\Api\RoomLocationMedia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomLocationMediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'room_location_id' => $this->room_location_id,
            'type' => [
                'key' => $this->type->value,
                'label' => $this->type->name,
            ],
            'file' => $this->file_link,
            'alt' => $this->alt,
            'title' => $this->title,
            'file_type' => [

                'key' => $this->file_type->value,
                'label' => $this->file_type->label(),
            ],
            'status' => (bool) $this->status,
        ];
    }
}
