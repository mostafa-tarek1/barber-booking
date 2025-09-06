<?php

namespace App\Modules\Auth\Http\Requests\Dashboard\Customer;

use App\Modules\Auth\Application\DTOS\Customer\UserDTO;
use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Base\Http\Rules\FileBase64OrFileRule;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends BaseRequestAbstract
{
    protected  $dtoClass = UserDTO::class;
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
            'word' => 'nullable|string',
            "is_blocked" => 'nullable|boolean',
            "is_verified" => 'nullable|boolean',
            "is_phone_verified" => 'nullable|boolean',
            "is_email_verified" => 'nullable|boolean',
            "is_approved" => 'nullable|boolean',
            'first_name' => 'nullable|string',
            'user_id' => 'required|integer|exists:users,id',
            'name' => 'nullable|string',
            'image' => ['nullable', new ImageBase64OrFileRule()],
            'username' => 'nullable|string|unique:users,username,' . $this->user_id,
            'phone' => 'required|digits:9|unique:users,phone,' . $this->user_id,
            // 'identify_number' => 'nullable|string|unique:users,id_number,' . $this->user_id,
            'email' => 'nullable|email|unique:users,email,' . $this->user_id,
            'password' => 'nullable|string|confirmed',
            'password_confirmation' => 'nullable|required_with:password|string',
            'commercial_register' => ['nullable', new FileBase64OrFileRule()],
            'tax_register' => ['nullable', new FileBase64OrFileRule()],
            'national_address' => ['nullable', 'array'],
            'national_address.address' => ['nullable', Rule::requiredIf($this->input('national_address')), 'string'],
            'national_address.file_address' => ['nullable', new FileBase64OrFileRule()],
            'national_address.lat' => ['nullable', 'string'],
            'national_address.lng' => ['nullable', 'string'],

            'another_address' => ['nullable', 'array'],
            'another_address.*.id' => ['nullable', 'integer', Rule::exists('user_addresses', 'id')->where('user_id', $this->user_id)->where('is_master', false)],
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

        $this->merge(array_filter([
            'username' => $this->username ? str_replace(' ', '', strtolower($this->username)) : null,
            'phone' => $this->phone ? str_replace(' ', '', $this->phone) : null,
            'email' => $this->email ? strtolower($this->email)  : null,
        ]));
    }
    /**
     * Get custom validation messages
     *
     * @return array
     */
    public function customMessages(): array
    {
        return [
            'another_address.*.file_address.required' => 'The file address field is required.',
            'another_address.*.id.exists' => 'The id field must be valid id',
        ]; // Override in child classes

    }
}
