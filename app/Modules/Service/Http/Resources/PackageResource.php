<?php

namespace App\Modules\Service\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class PackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $translations = collect(LaravelLocalization::getSupportedLanguagesKeys())
            ->mapWithKeys(fn (string $locale) => [
                $locale => [
                    'name' => $this->translateOrNew($locale)->name ?? '',
                    'description' => $this->translateOrNew($locale)->description ?? '',
                ],
            ])
            ->all();

        return [
            'id' => $this->id,
            'translations' => $translations,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'duration' => $this->duration,
            'image' => $this->image_link,
            'is_active' => (bool) $this->is_active,
            'services' => $this->services->map(fn ($service) => [
                'id' => $service->id,
                'name' => $service->name,
                'price' => $service->price,
                'duration' => $service->duration,
                'quantity' => (int) $service->pivot->quantity,
                'sort_order' => (int) $service->pivot->sort_order,
            ])->values(),
        ];
    }
}
