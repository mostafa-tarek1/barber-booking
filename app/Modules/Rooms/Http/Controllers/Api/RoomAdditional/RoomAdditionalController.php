<?php

namespace App\Modules\Rooms\Http\Controllers\Api\RoomAdditional;

use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Rooms\Http\Requests\RoomAdditional\RoomAdditionalIdRequest;
use App\Modules\Rooms\Http\Requests\RoomAdditional\FetchRoomAdditionalRequest;
use App\Modules\Rooms\Application\UseCases\RoomAdditional\RoomAdditionalUseCase;
use App\Http\Controllers\Controller;


class RoomAdditionalController extends Controller
{
    protected $roomAdditionalUseCase;

    public function __construct(RoomAdditionalUseCase $roomAdditionalUseCase)
    {
        $this->roomAdditionalUseCase = $roomAdditionalUseCase;
    }
    public function fetchRoomAdditionals(FetchRoomAdditionalRequest $request)
    {
        return $this->roomAdditionalUseCase->fetchRoomAdditionals($request->toDTO(), ViewTypeEnum::MOBILE->value)->response();
    }

    public function fetchRoomAdditionalDetails(RoomAdditionalIdRequest $request)
    {
        // dd($request->toDTO());
        return $this->roomAdditionalUseCase->fetchRoomAdditionalDetails($request->toDTO(), ViewTypeEnum::MOBILE->value)->response();
    }
}
