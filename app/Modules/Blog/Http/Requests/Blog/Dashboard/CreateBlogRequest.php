<?php

namespace App\Modules\Blog\Http\Requests\Blog\Dashboard;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Blog\Application\DTOS\Blog\BlogDTO;

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
        return [ // data validation
            'translations' => 'required|array',
            'organization_id' => 'nullable|integer',
            'slug' => 'nullable|string',
            'image' => 'nullable',
            'author' => 'nullable|string',
            'hashtags' => 'nullable|array',
            'hashtags.*' => 'integer|exists:hashtags,id',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
        ];
    }
}
