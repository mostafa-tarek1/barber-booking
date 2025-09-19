<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\AboutUs;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Statistics\Application\DTOS\AboutUs\AboutUsImageDTO;

class AboutUsImageIdRequest extends BaseRequestAbstract
{
    protected $dtoClass = AboutUsImageDTO::class;

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
            "about_us_image_id" => "required|exists:about_us_images,id",
        ];
    }

    /**
     * Format the validated data before using it.
     *
     * @return AboutUsImageDTO
     */
    public function formatted(): AboutUsImageDTO
    {
        return AboutUsImageDTO::fromArray($this->validated());
    }
}
