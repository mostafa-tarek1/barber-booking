<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\OurPartner;

use App\Modules\Base\Http\Rules\FileBase64OrFileRule;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use App\Modules\Statistics\Application\DTOS\OurPartner\OurPartnerDTO;
use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class CreateOurPartnerRequest extends BaseRequestAbstract
{
    protected $dtoClass = OurPartnerDTO::class;
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

        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $rules["translations.title_$locale"] = 'required|string|max:255';
        }
        $rules['image'] = ['nullable', new ImageBase64OrFileRule()];
        return $rules;
    }
}
