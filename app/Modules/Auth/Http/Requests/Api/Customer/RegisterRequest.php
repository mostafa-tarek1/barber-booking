<?php

namespace App\Modules\Auth\Http\Requests\Api\Customer;

use App\Modules\Auth\Application\DTOS\Customer\CustomerDTO;
use App\Modules\Auth\Application\DTOS\Customer\RegisterDTO;
use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\Customer;
use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Base\Http\Requests\Base\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends BaseRequestAbstract
{
    protected $dtoClass = RegisterDTO::class;
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
            'name' => 'required|string',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'username' => 'nullable|string|unique:users,username',
            'phone_code' => 'nullable|string', //country_code
            'phone' => 'required|digits:9|unique:users,phone',
            // 'identify_number' => 'required|string|unique:users,id_number',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string',
            'device_token' => 'nullable|string',
            'device_id' => 'nullable|string',
            'device_type' => 'nullable|string',
            "device_token" => 'nullable|string',
            'os_version' => 'nullable|string',
            'device_name' => 'nullable|string',
            'device_brand' => 'nullable|string',
            'device_model' => 'nullable|string',
            'location_id' => 'nullable|integer',
            'nationality_id' => 'nullable|integer',
        ];
    }

    /**

     * Prepare the data for validation.

     */

    protected function prepareForValidation(): void
    {

        $this->merge([
            'username' => $this->username ? str_replace(' ', '', strtolower($this->username)) : null,
            'phone' => str_replace(' ', '', $this->phone),
            'email' => $this->email ? strtolower($this->email) : null,
        ]);
    }

    /* public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data['username'] = str_replace(' ', '', strtolower($data['username']) ?? '');

        return $data;
    } */
}
