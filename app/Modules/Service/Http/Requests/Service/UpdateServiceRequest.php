<?php

namespace App\Modules\Service\Http\Requests\Service;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use App\Modules\Service\Application\DTOS\Service\ServiceDTO;
use Illuminate\Validation\Rule;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class UpdateServiceRequest extends BaseRequestAbstract
{
    protected $service_id;
    protected $dtoClass = ServiceDTO::class;

    public function authorize(): bool
    {
        return true;
    }


    public function customRules(): array
    {
        $rules = [];
        $rules['service_id'] = ['required', 'integer', 'exists:services,id'];

        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $rules["translations.name_$locale"] = [
                'sometimes',
                'string',
                'min:2',
                'max:255',
                Rule::unique('service_translations', 'name')
                    ->where('locale', $locale)
                    ->ignore($this->service_id, 'service_id'),
            ];
            $rules["translations.description_$locale"] = 'sometimes|string|min:2';
        }

        $rules['image'] = [
            'nullable',
            new ImageBase64OrFileRule(),
        ];
        $rules['price'] = ['sometimes', 'numeric', 'min:0'];
        $rules['duration'] = ['sometimes', 'integer', 'min:0'];
        $rules['is_active'] = ['sometimes', 'boolean'];
        return $rules;
    }
}
