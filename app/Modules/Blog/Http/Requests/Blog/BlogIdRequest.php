<?php

namespace App\Modules\Blog\Http\Requests\Blog;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Blog\Application\DTOS\Blog\BlogFilterDTO;


class BlogIdRequest extends BaseRequestAbstract
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
    public function customRules(): array
    {
        return [ // data validation
            'blog_id' => 'sometimes|required_without:slug|integer|exists:blogs,id',
            'slug' => 'sometimes|required_without:blog_id|string|exists:blogs,slug',
        ];
    }


}
