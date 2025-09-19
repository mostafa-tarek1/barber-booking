<?php

namespace App\Modules\Rooms\Http\Requests\RoomLocationMedia;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use App\Modules\Rooms\Application\DTOS\RoomLocationMedia\RoomLocationMediaDTO;
use Illuminate\Validation\Rule;
use App\Modules\Rooms\Domain\Enums\RoomLocationMediaTypeEnum;
use App\Modules\Rooms\Domain\Enums\RoomLocationMediaFileTypeEnum;

class UpdateRoomLocationMediaRequest extends BaseRequestAbstract
{
    protected $dtoClass = RoomLocationMediaDTO::class;

    public function authorize(): bool
    {
        return true;
    }

    public function customRules(): array
    {
        return [
            'room_location_media_id' => ['required', 'integer', 'exists:room_location_media,id'],
            'type' => ['nullable', Rule::enum(RoomLocationMediaTypeEnum::class)],
            'file' => [
                'nullable'],
            'title' => ['nullable', 'string', 'max:255'],
            'alt' => ['nullable', 'string', 'max:255'],
            'file_type' => ['nullable', Rule::enum(RoomLocationMediaFileTypeEnum::class)],
            'status' => ['boolean'],
        ];
    }
}
