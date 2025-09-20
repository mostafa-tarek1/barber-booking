<?php

namespace App\Modules\Blog\Http\Resources\Api\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class WebSiteCategoryDetailResource extends WebSiteCategoryResource
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
            'children' => WebSiteCategoryDetailResource::collection($this->children ?? collect()),
        ]);
    }
}
