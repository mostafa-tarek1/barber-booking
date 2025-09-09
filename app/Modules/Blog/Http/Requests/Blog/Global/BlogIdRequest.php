<?php

namespace App\Modules\Blog\Http\Requests\Blog\Global;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Blog\Application\DTOS\Blog\BlogDTO;
use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Blog\Application\DTOS\Blog\BlogFilterDTO;
use Illuminate\Validation\Rule;

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
    public function CustomRules(): array
    {
        return [ // data validation
            'blog_id' => [
                'nullable',
                'integer',
                Rule::exists('blogs', 'id'),
                'required_without:slug',
            ],
            'slug' => [
                'nullable',
                'string',
                Rule::exists('blogs', 'slug'),
                'required_without:blog_id',
            ],
        ];
    }
}
