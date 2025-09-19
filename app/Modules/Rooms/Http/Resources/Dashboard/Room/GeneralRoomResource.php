<?php

namespace App\Modules\Rooms\Http\Resources\Dashboard\Room;

use App\Modules\Auth\Http\Resources\Admin\AdminResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Hash;

class GeneralRoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $title = $request->header('Accept-Language')  !== "*" ? getTranslation('title', $request->header('Accept-Language'), $this) : getTranslationAndLocale($this?->translations, 'title');
        
        return [
            'id' => $this->id,
            is_array($title) ? 'titles' : 'title' => $title,
            "created_at" => $this->created_at->toDateTimeString(),
        ];
    }
}

