<?php

namespace App\Modules\Blog\Http\Requests\Hashtag;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Blog\Application\DTOS\Hashtag\HashtagDTO;
use Illuminate\Validation\Rule;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class CreateHashtagRequest extends BaseRequestAbstract
{
    protected $dtoClass = HashtagDTO::class;
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
            $rules["translations.title_$locale"] = [
                'required',
                'string',
                'min:2',
                Rule::unique('hashtag_translations', 'title')->where('locale', $locale),
            ];
        }
        $rules['slug'] = [
            'nullable',
            'string',
            'unique:hashtags,slug',
        ];
        return $rules;
    }



    public function prepareForValidation(): void
    {
        $this->merge([
            'slug' => str_replace(' ', '-', strtolower($this->slug)),
        ]);
    }
}
