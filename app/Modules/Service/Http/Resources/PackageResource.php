<?php

namespace App\Modules\Service\Http\Resources;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class PackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'translations' => $this->localizedFields($this->resource),
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'duration' => $this->duration,
            'image' => $this->image_link,
            'is_active' => (bool) $this->is_active,
            'services' => $this->services->map(fn ($service) => [
                'id' => $service->id,
                'translations' => $this->localizedFields($service),
                'name' => $service->translate('ar')?->name ?: $service->name,
                'price' => $service->price,
                'duration' => $service->duration,
                'quantity' => (int) $service->pivot->quantity,
                'sort_order' => (int) $service->pivot->sort_order,
            ])->values(),
        ];
    }

    private function localizedFields(TranslatableContract $translatable): array
    {
        return collect(LaravelLocalization::getSupportedLanguagesKeys())
            ->mapWithKeys(fn (string $locale) => [
                $locale => [
                    'name' => $translatable->translateOrNew($locale)->name ?? '',
                    'description' => $translatable->translateOrNew($locale)->description ?? '',
                ],
            ])
            ->all();
    }
}
