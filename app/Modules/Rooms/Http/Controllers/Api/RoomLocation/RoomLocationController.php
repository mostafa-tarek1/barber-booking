<?php

namespace App\Modules\Rooms\Http\Controllers\Api\RoomLocation;

use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Rooms\Application\UseCases\RoomLocation\RoomLocationUseCase;
use App\Modules\Rooms\Http\Requests\RoomLocation\FetchRoomLocationRequest;
use App\Modules\Rooms\Http\Requests\RoomLocation\RoomLocationIdRequest;
use App\Http\Controllers\Controller;


class RoomLocationController extends Controller
{
    protected $roomLocationUseCase;

    public function __construct(RoomLocationUseCase $roomLocationUseCase)
    {
        $this->roomLocationUseCase = $roomLocationUseCase;
    }
    public function fetchRoomLocations(FetchRoomLocationRequest $request)
    {
        return $this->roomLocationUseCase->fetchRoomLocations($request->toDTO(), ViewTypeEnum::MOBILE->value)->response();
    }
    public function fetchRoomLocationDetails(RoomLocationIdRequest $request)
    {
        // dd($request->toDTO());
        return $this->roomLocationUseCase->fetchRoomLocationDetails($request->toDTO(), ViewTypeEnum::MOBILE->value)->response();
    }
}
