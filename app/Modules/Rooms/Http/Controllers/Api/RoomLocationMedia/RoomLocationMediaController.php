<?php

namespace App\Modules\Rooms\Http\Controllers\Api\RoomLocationMedia;

use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Rooms\Application\UseCases\RoomLocationMedia\RoomLocationMediaUseCase;
use App\Modules\Rooms\Http\Requests\RoomLocationMedia\CreateRoomLocationMediaRequest;
use App\Modules\Rooms\Http\Requests\RoomLocationMedia\FetchRoomLocationMediaRequest;
use App\Modules\Rooms\Http\Requests\RoomLocationMedia\RoomLocationMediaIdRequest;
use App\Http\Controllers\Controller;
use App\Modules\Rooms\Http\Requests\RoomLocationMedia\UpdateRoomLocationMediaRequest;


class RoomLocationMediaController extends Controller
{
    protected $roomLocationMediaUseCase;

    public function __construct(RoomLocationMediaUseCase $roomLocationMediaUseCase)
    {
        $this->roomLocationMediaUseCase = $roomLocationMediaUseCase;
    }
    public function fetchRoomLocationMedias(FetchRoomLocationMediaRequest $request)
    {
        return $this->roomLocationMediaUseCase->fetchRoomLocationMedias($request->toDTO(), ViewTypeEnum::MOBILE->value)->response();
    }
    public function fetchRoomLocationMediaDetails(RoomLocationMediaIdRequest $request)
    {
        // dd($request->toDTO());
        return $this->roomLocationMediaUseCase->fetchRoomLocationMediaDetails($request->toDTO(), ViewTypeEnum::MOBILE->value)->response();
    }
}
