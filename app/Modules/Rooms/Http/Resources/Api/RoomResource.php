<?php

namespace App\Modules\Rooms\Http\Resources\Api;

use App\Modules\Auth\Http\Resources\Admin\AdminResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Hash;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $title = $request->header('Accept-Language') !== "*" ? getTranslation('title', $request->header('Accept-Language'), $this) : getTranslationAndLocale($this?->translations, 'title');
        $description = $request->header('Accept-Language') !== "*" ? getTranslation('description', $request->header('Accept-Language'), $this) : getTranslationAndLocale($this?->translations, 'description');

        $titles = getTranslationAndLocale($this?->translations, 'title');
        $descriptions = getTranslationAndLocale($this?->translations, 'description');
        return [
            'id' => $this->id,
            "created_at" => $this->created_at->toDateTimeString(),
            'titles' => $title ? $titles : [],
            'descriptions' => $description ? $descriptions : [],
            'price' => $this->price,
            'image' => $this->imageLink,
        ];
    }
}
