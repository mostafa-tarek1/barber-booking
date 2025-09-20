<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\ContactUs;

use App\Modules\Statistics\Application\DTOS\ContactUs\ContactUsDTO;
use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class CreateContactUsRequest extends BaseRequestAbstract
{
        protected $dtoClass = ContactUsDTO::class;
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
    public function CustomRules(): array
    {
        $rules = [
            'name' => 'required|string',
            'email' => 'required|email',
            'message' => 'required|string',
            'phone' => 'required|string|max:15',
        ];
        return $rules;
    }
}
