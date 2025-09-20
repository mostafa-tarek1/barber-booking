<?php

namespace App\Modules\Blog\Http\Requests\Blog;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use App\Modules\Blog\Application\DTOS\Blog\BlogDTO;
use Illuminate\Validation\Rule;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class CreateBlogRequest extends BaseRequestAbstract
{
    protected $dtoClass = BlogDTO::class;
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
            $rules["translations.title_$locale"] = [
                'required',
                'string',
                'min:2',
                'max:255',
            ];
            $rules["translations.subtitle_$locale"] = 'sometimes|string|min:2';
            $rules["translations.description_$locale"] = 'sometimes|string|min:2';
        }
        $rules['image'] = [
            'nullable',
            new ImageBase64OrFileRule(), // Custom rule to handle both base64 and file uploads
        ];
        // $rules['slug'] = 'nullable|string|unique:blogs,slug';
        $rules['hashtag_ids'] = 'nullable|array';
        $rules['hashtag_ids.*'] = 'integer|exists:hashtags,id';
        $rules['category_ids'] = 'nullable|array';
        $rules['category_ids.*'] = 'integer|exists:categories,id';
        return $rules;
    }

    // public function prepareForValidation(): void
    // {
    //     $this->merge([
    //         'slug' => str_replace(' ', '-', strtolower($this->slug)),
    //     ]);
    // }
}
