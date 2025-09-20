<?php

namespace App\Modules\Statistics\Http\Resources\Dashboard\AboutUs;

// use App\Modules\Statistics\Http\Resources\Dashboard\AboutUs\AboutUsStatisticsDetailResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AboutUsDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $titles = getAllTranslation(field: "title", model: $this);
        $descriptions = getAllTranslation(field: "description", model: $this);
        $sub_titles = getAllTranslation(field: "sub_title", model: $this);
        $our_mission = getAllTranslation(field: "our_mission", model: $this);
        $our_vision = getAllTranslation(field: "our_vision", model: $this);
        $our_goal = getAllTranslation(field: "our_goal", model: $this);
        return [
            'id' => $this->id,
            'title' => $titles,
            'description' => $descriptions,
            'sub_title' => $sub_titles,
            'our_mission' => $our_mission,
            'our_vision' => $our_vision,
            'our_goal' => $our_goal,
            'main_image' => $this->image_link,
            'statistics' => AboutUsStatisticsDetailResource::collection($this->statistics),
            'images' => AboutUsImageResource::collection($this->images),
        ];
    }
}
