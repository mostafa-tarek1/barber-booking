<?php

namespace App\Modules\Service\Http\Requests\Service;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Service\Application\DTOS\Service\ServiceFilterDTO;


class ServiceIdRequest extends BaseRequestAbstract
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
            'service_id' => 'sometimes|required_without:slug|integer|exists:services,id',
            'slug' => 'sometimes|required_without:service_id|string|exists:services,slug',
        ];
    }


}
