<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\OurPartner;

use App\Modules\Statistics\Application\DTOS\OurPartner\OurPartnerDTO;
use App\Modules\Statistics\Application\DTOS\Privacy\PrivacyDTO;
use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Base\Application\DTOS\Base\BaseDTO;
use App\Modules\Base\Domain\Request\BaseRequestAbstract;

class OurPartnerRequest extends BaseRequestAbstract
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
            'our_partner_id' => 'nullable|integer|exists:our_partners,id',
        ];
    }
}
