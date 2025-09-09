<?php

namespace App\Modules\Blog\Http\Requests\Category\Dashboard;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Blog\Application\DTOS\Category\CategoryDTO;
use Illuminate\Foundation\Http\FormRequest;

class FetchCategoryDetailsRequest extends BaseRequestAbstract
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
        return [ // data validation
            'category_id' => 'nullable|integer|exists:categorys,id',
        ];
    }
}
