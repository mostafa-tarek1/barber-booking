<?php

namespace App\Modules\Blog\Http\Requests\Blog;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Blog\Application\DTOS\Blog\BlogFilterDTO;
use Illuminate\Foundation\Http\FormRequest;

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
    public function customRules(): array
    {
        return [ // data validation
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'word' => 'nullable|string',
            'id' => 'nullable|integer',
            // allow filtering by single id or arrays (handled in use case)
            'category_id' => 'nullable',
            'hashtag_id' => 'nullable',
        ];
    }
}
