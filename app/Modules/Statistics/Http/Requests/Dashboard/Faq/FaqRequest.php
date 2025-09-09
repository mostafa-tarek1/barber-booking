<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\Faq;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;

class FaqRequest extends BaseRequestAbstract
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
            'faq_id' => 'nullable|integer|exists:faq,id',
        ];
    }
}
