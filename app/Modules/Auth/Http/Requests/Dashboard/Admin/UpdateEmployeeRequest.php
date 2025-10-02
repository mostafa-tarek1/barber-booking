<?php

namespace App\Modules\Auth\Http\Requests\Dashboard\Admin;

use App\Modules\Auth\Application\DTOS\Admin\EmployeeDTO;
use App\Modules\Auth\Http\Enums\EmployeeTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends BaseRequestAbstract
{
    protected $dtoClass = EmployeeDTO::class;
    public $authEmployee;
    /**
     * Determine if the Employee is authorized to make this request.
     */
    public function authorize(): bool
    {
        $this->authEmployee = $this->user();
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function customRules(): array
    {
        $employeeId = $this->input('employee_id'); // أو $this->route('employee_id')

        return [
            'employee_id' => [
                'required',
                'numeric',
                Rule::exists('employees', 'id')
                    ->where(function ($query) {
                        $query->where('id', '!=', $this->authEmployee->id)
                            ->where('employee_type', '!=', EmployeeTypeEnum::SUPER_ADMIN->value);
                    }),
            ],
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'username' => 'nullable|string',
            'phone' => [
                'nullable',
                'regex:/^01[0-9]{9}$/',
                'string',
                Rule::unique('employees', 'phone')->ignore($employeeId),
            ],
            'identify_number' => [
                'nullable',
                'string',
                Rule::unique('employees', 'identify_number')->ignore($employeeId),
            ],
            'email' => [
                'nullable',
                'email',
                Rule::unique('employees', 'email')->ignore($employeeId),
            ],
            'password' => 'nullable|string|min:8',
            'image' => [
                'nullable',
                new ImageBase64OrFileRule(),
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }

}



