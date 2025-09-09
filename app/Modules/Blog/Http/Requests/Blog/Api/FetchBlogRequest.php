<?php

namespace App\Modules\Blog\Http\Requests\Blog\Api;


use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Blog\Application\DTOS\Blog\BlogFilterDTO;

class FetchBlogRequest extends BaseRequestAbstract
{
    protected $dtoClass = BlogFilterDTO::class;
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
            'blog_id' => 'nullable|integer',
            'organization_id' => 'nullable|integer',
            'slug' => 'nullable|string',
        ];
    }
}
