<?php

namespace App\Modules\Statistics\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Statistics\Http\Resources\Api\About\AboutResource;

class MobileAboutDetailResource extends AboutResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
