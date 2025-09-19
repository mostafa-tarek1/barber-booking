<?php

namespace App\Modules\Rooms\Http\Requests\RoomAdditional;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Rooms\Application\DTOS\RoomAdditional\RoomAdditionalFilterDTO;


class RoomAdditionalIdRequest extends BaseRequestAbstract
{
    protected $dtoClass = RoomAdditionalFilterDTO::class;
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
        return [ // data validation
            'room_additional_id' => 'required|integer|exists:room_additionals,id',
        ];
    }


}
