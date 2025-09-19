<?php

namespace App\Modules\Rooms\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Rooms\Application\UseCases\Room\RoomUseCase;
use App\Modules\Rooms\Http\Requests\Room\FetchRoomRequest;
use App\Modules\Rooms\Http\Requests\Room\RoomIdRequest;

class RoomController extends Controller
{
    protected $roomUseCase;

    public function __construct(RoomUseCase $roomUseCase)
    {
        $this->roomUseCase = $roomUseCase;
    }

    /**
     * Fetch rooms list
     *
     * @param FetchRoomRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchRooms(FetchRoomRequest $request)
    {
        return $this->roomUseCase->fetchRooms($request->toDTO(), ViewTypeEnum::MOBILE->value)->response();
    }

    /**
     * Fetch room details
     *
     * @param RoomIdRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchRoomDetails(RoomIdRequest $request)
    {
        return $this->roomUseCase->fetchRoomDetails($request->toDTO(), ViewTypeEnum::MOBILE->value)->response();
    }
}
