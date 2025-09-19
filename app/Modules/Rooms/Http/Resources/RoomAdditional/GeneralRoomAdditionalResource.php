<?php

namespace App\Modules\Rooms\Http\Resources\RoomAdditional;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneralRoomAdditionalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $title = $request->header('Accept-Language') !== "*" ? getTranslation('title', $request->header('Accept-Language'), $this) : getTranslationAndLocale($this?->translations, 'title');
        return [
            'id' => $this->id,
            is_array($title) ? 'titles' : 'title' => $title,
            'is_active' => (bool) $this->is_active,
            'order' => $this->order,
        ];
    }
}
