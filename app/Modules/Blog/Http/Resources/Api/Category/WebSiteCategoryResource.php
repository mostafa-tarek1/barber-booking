<?php

namespace App\Modules\Blog\Http\Resources\Api\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebSiteCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $title = $request->header('Accept-Language')  !== "*" ? getTranslation('title', $request->header('Accept-Language'), $this) : getTranslationAndLocale($this?->translations, 'title');

        $titles =  getTranslationAndLocale($this?->translations, 'title');

        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'is_active' => boolval($this->is_active),
            "slug" => $this->slug ?? '',
            'image' => $this->image_link,
            'titles' => $title ? $titles : [],
        ];
    }
}
