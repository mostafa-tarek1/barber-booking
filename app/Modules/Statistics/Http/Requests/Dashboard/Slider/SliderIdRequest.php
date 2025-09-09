<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\Slider;


use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Statistics\Application\DTOS\Slider\SliderDTO;
use App\Modules\Statistics\Application\DTOS\Slider\SliderFilterDTO;

class SliderIdRequest extends BaseRequestAbstract
{
    protected $dtoClass = SliderFilterDTO::class;

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

        return [
            "slider_id" => "required|exists:sliders,id",
        ];

    }

    /**
     * Format the validated data before using it.
     *
     * @return SliderDTO
     */
    public function formatted(): SliderDTO
    {
        return SliderDTO::fromArray($this->validated());
    }
}
