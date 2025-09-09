<?php

namespace App\Modules\Blog\Http\Resources\Category;

use App\Modules\Auth\Http\Resources\Admin\AdminResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            "created_at" => $this->created_at->toDateTimeString(),
            'titles' => $title ? $titles : [],
            'created_by' => new AdminResource($this->creator),
        ];
    }
}
