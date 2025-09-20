<?php

namespace App\Modules\Blog\Http\Resources\Blog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class BlogDetailResource extends BlogResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        return array_merge($data, [
            'children' => BlogDetailResource::collection($this->children ?? collect()),
        ]);
    }
}
