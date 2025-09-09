<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\AboutUs;

use App\Modules\Statistics\Application\DTOS\AboutUs\AboutUsDTO;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;

class CreateOrUpdateAboutUsRequest extends BaseRequestAbstract
{
    protected $dtoClass = AboutUsDTO::class;

    public function authorize(): bool
    {
        return true;
    }

    public function CustomRules(): array
    {
        $rules = [];

        $isUpdate = $this->has('about_us_id');

        if ($isUpdate) {
            $rules['about_us_id'] = 'required|exists:about_us,id';
        }

        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $rules["translations.title_$locale"] = 'nullable|string|max:255';
            $rules["translations.sub_title_$locale"] = 'nullable|string|max:255';
            $rules["translations.description_$locale"] = 'nullable|string|max:65535';
            $rules["translations.our_mission_$locale"] = 'nullable|string|max:65535';
            $rules["translations.our_vision_$locale"] = 'nullable|string|max:65535';
            $rules["translations.our_goal_$locale"] = 'nullable|string|max:65535';
        }
        $rules['main_image'] = ['nullable', new ImageBase64OrFileRule];
        $rules['statistics'] = 'nullable|array';
        $rules['statistics.*.key'] = 'nullable|string|max:255';
        $rules['statistics.*.value'] = 'nullable|string|max:255';
        $rules['statistics.*.icon'] = ['nullable', new ImageBase64OrFileRule];

        $rules['images'] = 'nullable|array';
        $rules['images.*.file'] = ['nullable', new ImageBase64OrFileRule];
        $rules['images.*.alt'] = 'nullable|string|max:255';
        return $rules;
    }

}
