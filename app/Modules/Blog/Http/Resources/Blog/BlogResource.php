<?php

namespace App\Modules\Blog\Http\Resources\Blog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Blog\Http\Resources\Hashtag\HashtagResource;
use App\Modules\Blog\Http\Resources\Category\CategoryResource;
use App\Modules\Blog\Infrastructure\Persistence\Models\Category\Category;

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
        $sutitle = $request->header('Accept-Language') !== "*" ? getTranslation('subtitle', $request->header('Accept-Language'), $this) : getTranslationAndLocale($this?->translations, 'subtitle');
        $description = $request->header('Accept-Language') !== "*" ? getTranslation('description', $request->header('Accept-Language'), $this) : getTranslationAndLocale($this?->translations, 'description');
        return [
            'id' => $this->id,
            is_array($title) ? 'titles' : 'title' => $title,
            is_array($sutitle) ? 'subtitles' : 'subtitle' => $sutitle,
            is_array($description) ? 'descriptions' : 'description' => $description,
            'slug' => $this->slug,
            'image' => $this->image_link,
            'hashtags' => HashtagResource::collection($this->hashtags),
            'author' => $this->author ?? null,
            'categories' => CategoryResource::collection($this->categories),
            "created_at" => $this->created_at->format("Y-m-d") ?? null
        ];
    }
}
