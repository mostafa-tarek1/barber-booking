<?php

namespace App\Modules\Statistics\Http\Resources\Api\About;


use Illuminate\Http\Request;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Statistics\Http\Resources\Api\AboutEmail\AboutEmailResource;
use App\Modules\Statistics\Http\Resources\Api\AboutPhone\AboutPhoneResource;
use App\Modules\Statistics\Http\Resources\Api\AboutSocial\AboutSocialResource;

class AboutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $title = $request->header('Accept-Language') !== "*" ? getTranslation('title', $request->header('Accept-Language'), $this) : getTranslationAndLocale($this?->translations, 'answer');
        $description = $request->header('Accept-Language') !== "*" ? getTranslation('description', $request->header('Accept-Language'), $this) : getTranslationAndLocale($this?->translations, 'question');
        $location = $request->header('Accept-Language') !== "*" ? getTranslation('location', $request->header('Accept-Language'), $this) : getTranslationAndLocale($this?->translations, 'question');


        return [
            'id' => $this->id,
            'titles' => $title,
            'descriptions' => $description,
            'locations' => $location,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'working_hours' => isset($this->workingHours) ? $this->workingHours->map(function ($h) {
                $days = is_array($h->days) ? ($h->days['title'] ?? $h->days) : [];
                return [
                    'days' => ['title' => $days],
                    'from_time' => $h->from_time,
                    'to_time' => $h->to_time,
                ];
            })->toArray() : [],
            "phones" => AboutPhoneResource::collection($this->phones ?? []),
            "emails" => AboutEmailResource::collection($this->emails ?? []),
            "socials" => AboutSocialResource::collection($this->socials ?? [])
        ];
    }
}
