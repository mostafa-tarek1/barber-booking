<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\Slider;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Statistics\Application\DTOS\Slider\SliderDTO;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class UpdateSliderRequest extends BaseRequestAbstract
{
    protected $dtoClass = SliderDTO::class;
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
        $rules['slider_id'] = ['required', 'integer', 'exists:sliders,id'];
        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $rules["translations.title_$locale"] = 'nullable|string';
            $rules["translations.description_$locale"] = 'nullable|string';
            $rules["translations.sub_title_$locale"] = 'nullable|string';
        }
        return $rules;
    }
}
