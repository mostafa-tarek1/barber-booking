<?php

namespace App\Modules\Blog\Http\Resources\Hashtag;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HashtagDetailResource extends JsonResource
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
            'title' => $title ?? '',
        ];
    }
}
