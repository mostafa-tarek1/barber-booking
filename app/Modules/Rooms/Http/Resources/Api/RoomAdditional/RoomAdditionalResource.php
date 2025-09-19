<?php

namespace App\Modules\Rooms\Http\Resources\Api\RoomAdditional;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomAdditionalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $title = $request->header('Accept-Language') !== "*" ? getTranslation('title', $request->header('Accept-Language'), $this) : getTranslationAndLocale($this?->translations, 'title');
        $titles = getTranslationAndLocale($this?->translations, 'title');
        return [
            'id' => $this->id,
            'titles' => $title ? $titles : [],
            'room_id' => $this->room_id,
            'parent_id' => $this->parent_id,
            'image' => $this->image_link,
            'is_active' => (bool) $this->is_active,
            'order' => $this->order,
            'children' => RoomAdditionalResource::collection($this->whenLoaded('children')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
