<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\ContactUs;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;

class ContactUsRequest extends BaseRequestAbstract
{
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
            'contact_us_id' => 'nullable|integer|exists:contact_us,id',
        ];
    }
}
