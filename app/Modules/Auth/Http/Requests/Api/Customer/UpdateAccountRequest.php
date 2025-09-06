<?php

namespace App\Modules\Auth\Http\Requests\Api\Customer;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Auth\Application\DTOS\Customer\UpdateAccountDTO;
use App\Modules\Base\Http\Rules\FileBase64OrFileRule;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends BaseRequestAbstract
{
    protected  $dtoClass = UpdateAccountDTO::class;
    protected $authUser;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $this->authUser = $this->user();
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function customRules(): array
    {
            // dd($this->input('another_address'));

        return [ // data validation
            'name' => ['nullable', 'string'],
            'is_online' => ['nullable', 'boolean'],
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'username' => ['nullable', 'string', 'unique:users,username,' . $this->authUser->id],
            'email' => ['nullable', 'email', 'unique:users,email,' . $this->authUser->id],
            'phone_code' => 'nullable',
            'string',
            'phone' => ['nullable', 'string', 'unique:users,phone,' . $this->authUser->id],
            'location_id' => ['nullable', 'integer'],
            'nationality_id' => ['nullable', 'integer'],
            'allow_notification' => ['nullable', 'boolean'],
            'image' => ['nullable', new ImageBase64OrFileRule()],
            'cover' => ['nullable', new ImageBase64OrFileRule()],
            'commercial_register' => ['nullable', new FileBase64OrFileRule()],
            'tax_register' => ['nullable', new FileBase64OrFileRule()],
            'national_address' => ['nullable', 'array'],
            'national_address.address' => ['nullable', Rule::requiredIf($this->input('national_address')), 'string'],
            'national_address.lat' => ['nullable', 'string'],
            'national_address.lng' => ['nullable', 'string'],
            'national_address.file_address' => ['nullable', new FileBase64OrFileRule()],

            'another_address' => ['nullable', 'array'],
            'another_address.*.address' => ['nullable', Rule::requiredIf($this->input('another_address')), 'string'],
            'another_address.*.lat' => ['nullable', 'string'],
            'another_address.*.lng' => ['nullable',],
            'another_address.*.file_address' => ['nullable', new FileBase64OrFileRule()],
            'another_address.*.id' => ['nullable', 'integer', Rule::exists('user_addresses', 'id')->where(function ($query) {
                $query->where('user_id', $this->authUser->id);
            })],
        ];
    }

    /**

     * Prepare the data for validation.

     */

    protected function prepareForValidation(): void
    {

        $data = [];

        if ($this->username) {
            $data['username'] = str_replace(' ', '', strtolower($this->username));
        }
        if ($this->phone) {
            $data['phone'] = str_replace(' ', '', $this->phone);
        }
        if ($this->email) {
            $data['email'] = strtolower($this->email);
        }
        // if ($this->input('national_address.lon')) {
        //     $nationalAddresses = $this->input('national_address');
        //     $nationalAddresses['lng'] = $this->input('national_address.lon');
        //     $data['national_address'] = $nationalAddresses;
        // }
        // // Handle another_address.*.lon -> another_address.*.lng
        // if ($this->has('another_address') && is_array($this->input('another_address'))) {
        //     $anotherAddresses = $this->input('another_address');
        //     foreach ($anotherAddresses as $i => $address) {
        //         // dd($address);
        //         if (isset($address['lon'])) {
        //             $data['another_address'][$i]['lng'] = $address['lon'];
        //             // dd($data['another_address'][$i]);
        //         }
        //     }
        // }
        $this->merge($data);

        /* $this->merge(array_filter([
            'username' => $this->username ? str_replace(' ', '', strtolower($this->username)) : null,
            'phone' => $this->phone ? str_replace(' ', '', $this->phone) : null,
            'email' => $this->email ? strtolower($this->email)  : null,
            'national_address.lng' => $this->input('national_address.lon') ?? null,
            'another_address.*.lng' => $this->input('another_address.*.lon') ?? null,
        ])); */
    }
}
