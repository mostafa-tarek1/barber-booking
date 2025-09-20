<?php

namespace App\Modules\Statistics\Http\Resources\Api;


use App\Modules\Course\Http\Resources\CourseSetting\SettingResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Course\Http\Resources\Video\VideoResource;
use App\Modules\Course\Http\Resources\CoursePayment\CoursePaymentResource;

class MobileAboutResource extends JsonResource
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
        $titles = getTranslationAndLocale($this?->translations, 'answer');
        $descriptions = getTranslationAndLocale($this?->translations, 'question');

        return [
            'id' => $this->id,
            'titles' => $title ? $titles : [],
            'title' => $title ?? '',
            'descriptions' => $description ? $descriptions : [],
            'description' => $description ?? '',
        ];
    }
}
