<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\About;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Statistics\Application\DTOS\About\AboutFilterDTO;

class FetchAboutsRequest extends BaseRequestAbstract
{
        protected $dtoClass = AboutFilterDTO::class;
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
