<?php

namespace App\Modules\Statistics\Http\Resources\Dashboard\AboutUs;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AboutUsStatisticsDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'about_us_id'=> $this->about_us_id,
            'key'        => $this->key,
            'value'      => $this->value,
            'icon'       => $this->iconLink,
            // 'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            // 'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
