<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\Faq;

use App\Modules\Statistics\Application\DTOS\Faq\FaqDTO;
use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class CreateFaqRequest extends BaseRequestAbstract
{
        protected $dtoClass = FaqDTO::class;
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
            $rules["translations.answer_$locale"] = 'required|string|max:255';
            $rules["translations.question_$locale"] = 'required|string|max:255';
        }
        $rules['is_active'] = 'nullable|boolean';
        $rules['order'] = 'nullable|integer';
        return $rules;
    }
}
