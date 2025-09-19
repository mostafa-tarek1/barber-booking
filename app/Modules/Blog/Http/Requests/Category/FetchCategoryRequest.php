<?php

namespace App\Modules\Blog\Http\Requests\Category;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Blog\Application\DTOS\Category\CategoryFilterDTO;
use Illuminate\Foundation\Http\FormRequest;

class FetchCategoryRequest extends BaseRequestAbstract
{
    protected $dtoClass = CategoryFilterDTO::class;
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
            'category_id' => 'nullable|integer',
            'parent_id' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'is_parent' => 'nullable|boolean'
        ];
    }
}
