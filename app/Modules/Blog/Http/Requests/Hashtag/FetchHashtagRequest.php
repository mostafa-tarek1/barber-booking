<?php

namespace App\Modules\Blog\Http\Requests\Hashtag;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Blog\Application\DTOS\Hashtag\HashtagFilterDTO;

class FetchHashtagRequest extends BaseRequestAbstract
{
    protected $dtoClass = HashtagFilterDTO::class;
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
            'word' => 'nullable|string',
            'parent_id' => 'nullable|integer',
        ];
    }
}
