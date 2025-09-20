<?php

namespace App\Modules\Statistics\Http\Resources\Dashboard\About;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AboutSocialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            "id" => $this->id ?? "",
            "title" => $this->key ?? "",
            $this->key ?? ""  =>  $this->value  ?? "",
            "icon" => $this->image_link
        ];
    }
}
