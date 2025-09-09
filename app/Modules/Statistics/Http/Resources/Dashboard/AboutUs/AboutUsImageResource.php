<?php

namespace App\Modules\Statistics\Http\Resources\Dashboard\AboutUs;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AboutUsImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id ?? null,
            'file' => $this->file_link,
            'alt'  => $this->alt,
        ];
    }
}
