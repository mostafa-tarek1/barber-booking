<?php

namespace App\Modules\Blog\Http\Requests\Hashtag\Api;


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
    public function CustomRules(): array
    {
        return [ // data validation
            'hashtag_id' => 'nullable|integer',
            'organization_id' => 'nullable|integer',
            'slug' => 'nullable|string',
        ];
    }
}
