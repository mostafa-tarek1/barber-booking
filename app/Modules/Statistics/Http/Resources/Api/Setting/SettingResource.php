<?php

namespace App\Modules\Statistics\Http\Resources\Api\Setting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ?? 0,
            'tax_amount' => $this->tax_amount ?? 0,
            'tax_type' => $this->tax_type ?? 0,
        ];
    }
}
