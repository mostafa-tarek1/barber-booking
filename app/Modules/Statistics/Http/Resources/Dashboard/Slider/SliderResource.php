<?php

namespace App\Modules\Statistics\Http\Resources\Dashboard\Slider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
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
        $sub_title = $request->header('Accept-Language') !== "*" ? getTranslation('sub_title', $request->header('Accept-Language'), $this) : getTranslationAndLocale($this?->translations, 'sub_title');
        $titles = getTranslationAndLocale($this?->translations, 'title');
        $descriptions = getTranslationAndLocale($this?->translations, 'description');
        $sub_titles = getTranslationAndLocale($this?->translations, 'sub_title');

        return [
            'id' => $this->id,
            'titles' => $title ? $titles : [],
            'title' => $title ?? '',
            'descriptions' => $description ? $descriptions : [],
            'description' => $description ?? '',
            'sub_title' => $sub_title ?? '',
            'sub_titles' => $sub_title ? $sub_titles : [],
            'image' => $this->image_link,
        ];
    }
}
