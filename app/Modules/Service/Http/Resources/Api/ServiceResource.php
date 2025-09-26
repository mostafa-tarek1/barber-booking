<?php

namespace App\Modules\Service\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $translations = [];

        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $translations[$locale] = [
                'name' => $this->translateOrNew($locale)->name ?? '',
                'description' => $this->translateOrNew($locale)->description ?? '',
            ];
        }

        return [
            'id'        => $this->id,
            'translations' => $translations,
            'image'     => $this->image_link,
            'price'     => $this->price,
            'duration'  => $this->duration,
            'is_active' => (bool) $this->is_active,
            'created_at'=> $this->created_at?->toDateTimeString(),
            'updated_at'=> $this->updated_at?->toDateTimeString(),
        ];
    }
}
