<?php

namespace App\Modules\Statistics\Http\Resources\Api\AboutSocial;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Statistics\Domain\Enums\SocialKeyEnum;

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
            'id' => $this->id,
            $this->key ?? ""  =>  $this->value  ?? "",
            'key' => SocialKeyEnum::tryFromLabel(strtolower($this->key))->value ?? "",
            'icon' => $this->image_link,
            'value' => $this->value ?? "",
        ];
    }
}
