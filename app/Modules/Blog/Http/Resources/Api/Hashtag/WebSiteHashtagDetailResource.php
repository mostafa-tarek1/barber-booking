<?php

namespace App\Modules\Blog\Http\Resources\Api\Hashtag;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class WebSiteHashtagDetailResource extends WebSiteHashtagResource
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
            'children' => WebSiteHashtagDetailResource::collection($this->children ?? collect()),
        ]);
    }
}
