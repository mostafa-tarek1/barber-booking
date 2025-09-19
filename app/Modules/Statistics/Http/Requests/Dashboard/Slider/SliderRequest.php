<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\Slider;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;

class SliderRequest extends BaseRequestAbstract
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
            'slider_id' => 'nullable|integer|exists:sliders,id',
        ];
    }
}
