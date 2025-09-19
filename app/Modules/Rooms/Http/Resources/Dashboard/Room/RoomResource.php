<?php

namespace App\Modules\Rooms\Http\Resources\Dashboard\Room;

use App\Modules\Auth\Http\Resources\Admin\AdminResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Hash;

class RoomResource extends GeneralRoomResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $description = $request->header('Accept-Language')  !== "*" ? getTranslation('description', $request->header('Accept-Language'), $this) : getTranslationAndLocale($this?->translations, 'description');
        $data = parent::toArray($request);
        return array_merge($data, [
            is_array($description) ? 'descriptions' : 'description' => $description,
            'price' => $this->price,
            'image' => $this->imageLink,
        ]);
    }
}

