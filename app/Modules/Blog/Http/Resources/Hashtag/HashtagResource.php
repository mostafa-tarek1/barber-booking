<?php

namespace App\Modules\Blog\Http\Resources\Hashtag;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HashtagResource extends JsonResource
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
            'image' => $this->image_link,

        ];
    }
}
