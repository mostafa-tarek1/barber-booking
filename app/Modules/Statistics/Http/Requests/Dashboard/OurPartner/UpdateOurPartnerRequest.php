<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\OurPartner;

use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use App\Modules\Statistics\Application\DTOS\OurPartner\OurPartnerDTO;
use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class UpdateOurPartnerRequest extends BaseRequestAbstract
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
    public function customRules(): array
    {
        $rules = [];
        $rules['our_partner_id'] = ['required', 'integer', 'exists:our_partners,id'];
        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $rules["translations.title_$locale"] = 'nullable|string';
        }
        $rules['image'] = ['nullable', new ImageBase64OrFileRule()];
        return $rules;
    }
}
