<?php

namespace App\Modules\Blog\Http\Requests\Category;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use App\Modules\Blog\Application\DTOS\Category\CategoryDTO;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class UpdateCategoryRequest extends BaseRequestAbstract
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
    public function customRules(): array
    {
        $rules = [];
        $rules['category_id'] = ['required', 'integer', 'exists:categories,id'];
        $rules['is_active'] = ['nullable', 'boolean'];
        foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties) {
            $rules["translations.title_$localeCode"] = [
                'sometimes',
                'string',
                'min:2',
                'max:255',
                Rule::unique('category_translations', 'title')->where('locale', $localeCode)->ignore($this->category_id, 'category_id'),
            ];
            $rules["translations.subtitle_$localeCode"] = 'sometimes|string|min:2';
            $rules["translations.description_$localeCode"] = 'sometimes|string|min:2';
        }
        $rules['parent_id'] = 'nullable|integer|exists:categories,id';
        $rules['image'] = [
            'nullable',
            new ImageBase64OrFileRule(), // Custom rule to handle both base64 and file uploads
        ];
        $rules['order'] = 'nullable|integer';
        $rules['slug'] = [
            'nullable',
            'string',
            'unique:categories,slug,' . $this->category_id,
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
