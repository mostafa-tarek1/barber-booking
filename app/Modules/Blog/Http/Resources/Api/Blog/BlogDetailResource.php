<?php

namespace App\Modules\Blog\Http\Resources\Api\Blog;

use App\Modules\Blog\Http\Resources\Category\CategoryResource;
use App\Modules\Blog\Http\Resources\Hashtag\HashtagResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'content' => $this->content,
            'image' => $this->image,
            'status' => $this->status,
            'publish_date' => $this->publish_date?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            // Include relationships if needed
            'hashtags' => HashtagResource::collection($this->hashtags ?? collect()),
            'categories' => CategoryResource::collection($this->categories ?? collect()),
            // Add other fields as needed
        ];
    }
}
