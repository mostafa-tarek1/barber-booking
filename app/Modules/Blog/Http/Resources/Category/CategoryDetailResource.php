<?php

namespace App\Modules\Blog\Http\Resources\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class CategoryDetailResource extends CategoryResource
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
            'children' => CategoryDetailResource::collection($this->children ?? collect()),
        ]);
    }
}
