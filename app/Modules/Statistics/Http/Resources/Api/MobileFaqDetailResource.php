<?php

namespace App\Modules\Statistics\Http\Resources\Api;


use App\Modules\Course\Http\Resources\CourseSetting\SettingResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileFaqDetailResource extends MobileFaqResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
        /* $title = $request->header('Accept-Language')  !== "*" ? getTranslation('title', $request->header('Accept-Language'), $this) : getTranslationAndLocale($this?->translations, 'title');
        $subtitle = $request->header('Accept-Language')  !== "*" ? getTranslation('subtitle', $request->header('Accept-Language'), $this) : getTranslationAndLocale($this?->translations, 'subtitle');
        $description = $request->header('Accept-Language')  !== "*" ? getTranslation('description', $request->header('Accept-Language'), $this) : getTranslationAndLocale($this?->translations, 'description');

        $titles =  getTranslationAndLocale($this?->translations, 'title');
        $subtitles =  getTranslationAndLocale($this?->translations, 'subtitle');
        $descriptions =  getTranslationAndLocale($this?->translations, 'description');
        return [
            'id' => $this->id,
            'titles' => $title ? $titles : [],
            'title' => $title ?? '',
            'subtitles' => $subtitle ? $subtitles : [],
            'subtitle' => $subtitle ?? '',
            'descriptions' =>$description ? $descriptions : [],
            'description' => $description ?? '',

        ]; */
    }
}
