<?php

namespace App\Modules\Rooms\Http\Requests\RoomLocationMedia;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Rooms\Application\DTOS\RoomLocationMedia\RoomLocationMediaFilterDTO;

class FetchRoomLocationMediaRequest extends BaseRequestAbstract
{
    protected $dtoClass = RoomLocationMediaFilterDTO::class;
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
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'word' => 'nullable|string',
            'room_location_id' => 'nullable|integer',
        ];
    }
}
