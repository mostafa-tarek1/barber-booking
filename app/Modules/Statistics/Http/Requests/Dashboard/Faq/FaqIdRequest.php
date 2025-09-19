<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\Faq;


use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Statistics\Application\DTOS\Faq\FaqDTO;
use App\Modules\Statistics\Application\DTOS\Faq\FaqFilterDTO;

class FaqIdRequest extends BaseRequestAbstract
{
    protected $dtoClass = FaqFilterDTO::class;

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
            "faq_id" => "required|exists:faqs,id",
        ];

    }

    /**
     * Format the validated data before using it.
     *
     * @return FaqDTO
     */
    public function formatted(): FaqDTO
    {
        return FaqDTO::fromArray($this->validated());
    }
}
