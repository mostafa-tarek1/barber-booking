<?php

namespace App\Modules\Service\Http\Requests\Service;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Service\Application\DTOS\Service\ServiceFilterDTO;
use Illuminate\Foundation\Http\FormRequest;

class FetchServiceRequest extends BaseRequestAbstract
{
    protected $dtoClass = ServiceFilterDTO::class;
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
            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'word' => 'nullable|string',
            'id' => 'nullable|integer',
        ];
    }
}
