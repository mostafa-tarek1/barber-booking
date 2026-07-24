<?php

namespace App\Modules\Service\Http\Requests\Service;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use App\Modules\Service\Application\DTOS\Service\ServiceDTO;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class CreateServiceRequest extends BaseRequestAbstract
{
    protected $dtoClass = ServiceDTO::class;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function CustomRules(): array
    {
        $rules = [];
        $rules['translations'] = 'required|array';
        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $rules["translations.name_$locale"] = [
                'required',
                'string',
                'min:2',
                'max:255',
            ];
            $rules["translations.description_$locale"] = ['nullable', 'string', 'max:2000'];
        }
        $rules['image'] = [
            'nullable',
            new ImageBase64OrFileRule(), // Custom rule to handle both base64 and file uploads
        ];
        $rules['price'] = [
            'required',
            'numeric',
            'min:0',
        ];
        $rules['duration'] = [
            'required',
            'integer',
            'min:1',
        ];
        $rules['is_active'] = 'boolean';
        return $rules;
    }
}
