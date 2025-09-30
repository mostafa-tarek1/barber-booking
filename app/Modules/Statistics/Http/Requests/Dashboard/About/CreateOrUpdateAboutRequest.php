<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\About;

use App\Modules\Statistics\Application\DTOS\About\AboutDTO;
use Illuminate\Validation\Rule;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use App\Modules\Statistics\Domain\Enums\SocialKeyEnum;

class CreateOrUpdateAboutRequest extends BaseRequestAbstract
{
    public function authorize(): bool
    {
        return true;
    }

    public function CustomRules(): array
    {
        $rules = [];

        $isUpdate = $this->has('about_id');

        if ($isUpdate) {
            $rules['about_id'] = 'required|exists:abouts,id';
        }

        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $rules["translations.title_$locale"] = 'nullable|string|max:255';
            $rules["translations.description_$locale"] = 'nullable|string|max:65535';
            $rules["translations.location_$locale"] = 'nullable|string|max:65535';
        }

        $rules['lat'] = 'nullable|numeric';
        $rules['lng'] = 'nullable|numeric';

        $rules['working_hours'] = 'nullable|array';
        $rules['working_hours.*.days'] = 'nullable|array';
        $rules['working_hours.*.days.title'] = 'nullable|array|min:1';
        $rules['working_hours.*.days.title.*'] = 'nullable|string|max:50';
        $rules['working_hours.*.days.from_time'] = 'nullable|date_format:H:i';
        $rules['working_hours.*.days.to_time'] = 'nullable|date_format:H:i|after:working_hours.*.days.from_time';

        $rules['phones'] = 'nullable|array';
        $rules['phones.*'] = [
            "nullable",
            Rule::requiredIf($this->has('phones')),
            'string',
        ];
        $rules['emails'] = 'nullable|array';
        $rules['emails.*'] = [
            "nullable",
            Rule::requiredIf($this->has('emails')),
            'email',
        ];
        $rules['socials'] = 'nullable|array';
        $rules['socials.*.id'] = 'nullable|exists:about_socials,id';
        $rules['socials.*.key'] = [
            "required",
            "string",
            // Rule::unique('about_socials', 'key'),
            // Rule::enum(SocialKeyEnum::class),
        ];
        $rules['socials.*.value'] = 'nullable|string';
        $rules['socials.*.image'] = ['nullable', new ImageBase64OrFileRule];

        $rules['image'] = ['nullable', new ImageBase64OrFileRule];

        return $rules;
    }

    public function toDTO(): AboutDTO
    {
        return new AboutDTO($this->validated());
    }
}
