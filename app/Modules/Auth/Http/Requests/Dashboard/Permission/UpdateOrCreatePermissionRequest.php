<?php

namespace App\Modules\Auth\Http\Requests\Dashboard\Permission;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Auth\Application\DTOS\Permission\PermissionDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrCreatePermissionRequest extends BaseRequestAbstract
{
    protected  $dtoClass = PermissionDTO::class;
    /**
     * Determine if the Customer is authorized to make this request.
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
            "employee_id" => "required|exists:employees,id",
            "permissions" => "required|array",
            "permissions.*" => "required|string",
        ];
    }

}
