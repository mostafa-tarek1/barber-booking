<?php

namespace App\Modules\Blog\Http\Requests\Hashtag;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use App\Modules\Blog\Application\DTOS\Hashtag\HashtagDTO;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class UpdateHashtagRequest extends BaseRequestAbstract
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
    public function customRules(): array
    {
        // dd($this->Hashtag_id);
        $rules = [];
        $rules['hashtag_id'] = ['required', 'integer', 'exists:hashtags,id'];
        foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties) {
            $rules["translations.title_$localeCode"] = [
                'sometimes',
                'string',
                'min:2',
                Rule::unique('hashtag_translations', 'title')->where('locale', $localeCode)->ignore($this->hashtag_id,'hashtag_id'),
            ];
        }
        $rules['parent_id'] = 'nullable|integer|exists:hashtags,id';
        return $rules;
    }



    public function prepareForValidation(): void
    {
        $this->merge([
            'slug' => str_replace(' ', '-', strtolower($this->slug)),
        ]);
    }
}
