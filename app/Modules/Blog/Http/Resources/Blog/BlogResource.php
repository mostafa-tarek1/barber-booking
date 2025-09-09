<?php

namespace App\Modules\Blog\Http\Resources\Blog;

use App\Modules\Auth\Http\Resources\Admin\AdminResource;
use App\Modules\Blog\Http\Resources\Category\CategoryResource;
use App\Modules\Blog\Http\Resources\Hashtag\HashtagResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Hash;

class BlogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $title = $request->header('Accept-Language') !== "*" ? getTranslation('title', $request->header('Accept-Language'), $this) : getTranslationAndLocale($this?->translations, 'title');
        $subtitle = $request->header('Accept-Language') !== "*" ? getTranslation('subtitle', $request->header('Accept-Language'), $this) : getTranslationAndLocale($this?->translations, 'subtitle');
        $description = $request->header('Accept-Language') !== "*" ? getTranslation('description', $request->header('Accept-Language'), $this) : getTranslationAndLocale($this?->translations, 'description');

        $titles = getTranslationAndLocale($this?->translations, 'title');
        $subtitles = getTranslationAndLocale($this?->translations, 'subtitle');
        $descriptions = getTranslationAndLocale($this?->translations, 'description');
        return [
            'id' => $this->id,
            "slug" => $this->slug ?? '',
            'image' => $this->image_link,
            "created_at" => $this->created_at->toDateTimeString(),
            'titles' => $title ? $titles : [],
            'hashtags' => HashtagResource::collection($this->hashtags ?? collect()),
            'categories' => CategoryResource::collection($this->categories ?? collect()),
            'subtitles' => $subtitle ? $subtitles : [],
            'descriptions' => $description ? $descriptions : [],
            'created_by' => new AdminResource($this->creator),
        ];
    }
}
