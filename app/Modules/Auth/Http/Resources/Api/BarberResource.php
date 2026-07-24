<?php

namespace App\Modules\Auth\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BarberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => trim("{$this->first_name} {$this->last_name}"),
            'image' => $this->image_link,
        ];
    }
}
