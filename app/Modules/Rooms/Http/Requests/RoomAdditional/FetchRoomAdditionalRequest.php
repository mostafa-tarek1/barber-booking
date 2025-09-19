<?php

namespace App\Modules\Rooms\Http\Requests\RoomAdditional;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Rooms\Application\DTOS\RoomAdditional\RoomAdditionalFilterDTO;

class FetchRoomAdditionalRequest extends BaseRequestAbstract
{
    protected $dtoClass = RoomAdditionalFilterDTO::class;

    public function authorize(): bool
    {
        return true;
    }

    public function CustomRules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'word' => 'nullable|string|max:255',
            'room_id' => 'nullable|integer|exists:rooms,id',
            'is_active' => 'nullable|boolean',
            'parent_id' => 'nullable|integer|exists:room_additionals,id',
        ];
    }
}
