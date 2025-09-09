<?php

namespace App\Modules\Blog\Http\Requests\Blog;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use App\Modules\Blog\Application\DTOS\Blog\BlogDTO;
use Illuminate\Validation\Rule;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class UpdateBlogRequest extends BaseRequestAbstract
{
    protected $dtoClass = BlogDTO::class;

    public function authorize(): bool
    {
        return true;
    }

    public function customRules(): array
    {
        $rules = [];
        $rules['blog_id'] = ['required', 'integer', 'exists:blogs,id'];

        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $rules["translations.title_$locale"] = [
                'sometimes',
                'string',
                'min:2',
                'max:255',
                Rule::unique('blog_translations', 'title')
                    ->where('locale', $locale)
                    ->ignore($this->blog_id, 'blog_id'),
            ];
            $rules["translations.subtitle_$locale"] = 'sometimes|string|min:2';
            $rules["translations.description_$locale"] = 'sometimes|string|min:2';
        }

        $rules['image'] = [
            'nullable',
            new ImageBase64OrFileRule(),
        ];

        $rules['slug'] = [
            'nullable',
            'string',
            Rule::unique('blogs', 'slug')->ignore($this->blog_id),
        ];

        $rules['hashtag_ids'] = 'nullable|array';
        $rules['hashtag_ids.*'] = 'integer|exists:hashtags,id';

        $rules['category_ids'] = 'nullable|array';
        $rules['category_ids.*'] = 'integer|exists:categories,id';
        return $rules;
    }

    public function prepareForValidation(): void
    {
        if ($this->slug) {
            $this->merge([
                'slug' => str_replace(' ', '-', strtolower($this->slug)),
            ]);
        }
    }
}
