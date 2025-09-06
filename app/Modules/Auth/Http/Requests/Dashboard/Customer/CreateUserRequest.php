<?php

namespace App\Modules\Auth\Http\Requests\Dashboard\Customer;

use App\Modules\Auth\Application\DTOS\Customer\CustomerDTO;
use App\Modules\Auth\Application\DTOS\Customer\UserDTO;
use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Base\Http\Rules\FileBase64OrFileRule;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends BaseRequestAbstract
{
    protected  $dtoClass = UserDTO::class;
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
            "name" => "required|string",
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'phone' => 'required|digits:9|unique:users,phone',
            'identify_number' => 'nullable|string',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'confirmed',
            ],
            'password_confirmation' => 'required|string',
            'image' => ['nullable', new ImageBase64OrFileRule()],
            'commercial_register' => ['nullable', new FileBase64OrFileRule()],
            'tax_register' => ['nullable', new FileBase64OrFileRule()],
            'national_address' => ['nullable', 'array'],
            'national_address.address' => ['nullable', Rule::requiredIf($this->input('national_address')), 'string'],
            'national_address.file_address' => ['nullable', new FileBase64OrFileRule()],
            'national_address.lat' => ['nullable', 'string'],
            'national_address.lng' => ['nullable', 'string'],

            'another_address' => ['nullable', 'array'],
            'another_address.*.address' => ['nullable', Rule::requiredIf($this->input('another_address')), 'string'],
            'another_address.*.lat' => ['nullable', 'string'],
            'another_address.*.lng' => ['nullable',],
            'another_address.*.file_address' => ['nullable', new FileBase64OrFileRule()],
        ];
    }


    /**

     * Prepare the data for validation.

     */

    protected function prepareForValidation(): void
    {

        $this->merge([
            'username' => $this->username ? str_replace(' ', '', strtolower($this->username)) : null,
            'phone' => $this->phone ? str_replace(' ', '', $this->phone) : null,
            'email' => $this->email ? strtolower($this->email)  : null,
        ]);
    }

}
