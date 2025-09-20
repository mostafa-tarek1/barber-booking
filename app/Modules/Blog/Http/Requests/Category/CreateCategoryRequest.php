<?php

namespace App\Modules\Blog\Http\Requests\Category;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use App\Modules\Blog\Application\DTOS\Category\CategoryDTO;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class CreateCategoryRequest extends BaseRequestAbstract
{
    protected $dtoClass = CategoryDTO::class;
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

        $rules['is_active'] = ['nullable', 'boolean'];
                $rules['translations'] = 'required|array';
        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $rules["translations.title_$locale"] = [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('category_translations', 'title')->where('locale', $locale),
            ];
        }
        $rules['parent_id'] = 'nullable|integer|exists:categories,id';
        $rules['order'] = 'nullable|integer';
        $rules['products_ids'] = [
            'nullable',
            'array',
        ];
        $rules['image'] = [
            'nullable',
            new ImageBase64OrFileRule(), // Custom rule to handle both base64 and file uploads
        ];
        $rules['slug'] = 'nullable|string|unique:categories,slug';
        return $rules;

    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'slug' => str_replace(' ', '-', strtolower($this->slug)),
        ]);
    }
}
