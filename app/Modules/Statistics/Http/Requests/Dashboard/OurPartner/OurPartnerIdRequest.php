<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\OurPartner;


use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Base\Application\DTOS\Base\BaseDTO;
use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Statistics\Application\DTOS\OurPartner\OurPartnerDTO;
use App\Modules\Statistics\Application\DTOS\OurPartner\OurPartnerFilterDTO;

class OurPartnerIdRequest extends BaseRequestAbstract
{
    protected $dtoClass = OurPartnerFilterDTO::class;

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
            "our_partner_id" => "required|exists:our_partners,id",
        ];

    }

    /**
     * Format the validated data before using it.
     *
     * @return OurPartnerDTO
     */
    public function formatted(): OurPartnerDTO
    {
        return OurPartnerDTO::fromArray($this->validated());
    }
}
