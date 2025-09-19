<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\Faq;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Statistics\Application\DTOS\Faq\FaqFilterDTO;

class FetchFaqRequest extends BaseRequestAbstract
{
        protected $dtoClass = FaqFilterDTO::class;
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
    public function rules(): array
    {
        return [ // data validation
            'paginate' => 'nullable|boolean',
            "per_page" => 'nullable|integer',
            "word" => 'nullable|string',
        ];
    }


}
