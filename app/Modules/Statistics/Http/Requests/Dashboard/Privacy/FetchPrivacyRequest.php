<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\Privacy;

use App\Modules\Statistics\Application\DTOS\Faq\FaqDTO;
use App\Modules\Statistics\Application\DTOS\Privacy\PrivacyDTO;
use App\Modules\Statistics\Infrastructure\Persistence\Models\Privacy\Privacy;
use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Base\Application\DTOS\Base\BaseDTO;
use App\Modules\Base\Domain\Request\BaseRequestAbstract;

class FetchPrivacyRequest extends BaseRequestAbstract
{
    protected $dtoClass = PrivacyDTO::class;

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
            'paginate' => 'nullable|boolean',
            "per_page" => 'nullable|integer',
            "word" => 'nullable|string',
        ];
    }

}
