<?php

namespace App\Modules\Rooms\Http\Requests\RoomLocation;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Rooms\Application\DTOS\RoomLocation\RoomLocationFilterDTO;


class RoomLocationIdRequest extends BaseRequestAbstract
{
    protected $dtoClass = RoomLocationFilterDTO::class;
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
    public function customRules(): array
    {
        return [ // data validation
            'room_location_id' => 'required|integer|exists:room_locations,id',
        ];
    }


}
