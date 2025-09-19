<?php

namespace App\Modules\Rooms\Application\UseCases\Room;

use App\Modules\Base\Domain\Holders\AuthHolder;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Rooms\Application\DTOS\Room\RoomDTO;
use App\Modules\Rooms\Http\Resources\Dashboard\Room\RoomDetailResource;
use App\Modules\Rooms\Http\Resources\Api\RoomDetailResource as MobileDetailsResource;
use App\Modules\Rooms\Http\Resources\Dashboard\Room\RoomResource;
use App\Modules\Rooms\Http\Resources\Api\RoomResource as MobileResource;
use App\Modules\Rooms\Infrastructure\Persistence\Repositories\Room\RoomRepository;
use App\Modules\Rooms\Application\DTOS\Room\RoomFilterDTO;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Rooms\Infrastructure\Persistence\Models\Room\Room;

class RoomUseCase
{

    protected $roomRepository;
    protected $employee;
    public function __construct(RoomRepository $roomRepository)
    {
        $this->roomRepository = $roomRepository;
        $this->employee = AuthHolder::getInstance()->getAuth(AuthGurdTypeEnum::EMPLOYEE->value);
    }

    public function fetchRooms(RoomFilterDTO $roomFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            // dd($roomFilterDto);
            $rooms = $this->roomRepository->filter(
                $roomFilterDto,
                operator: 'like',
                translatableFields: ['title', 'description'],
                paginate: $roomFilterDto->paginate,
                limit: $roomFilterDto->limit
            );
            // dd($rooms);
            $resource = $this->HandelRoomResource(
                $rooms,
                $view,
                $roomFilterDto->paginate
            );
            return DataSuccess(
                status: true,
                message: 'Rooms fetched successfully',
                data: $roomFilterDto->paginate ? $resource->response()->getData() : $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function fetchRoomDetails(RoomFilterDTO $roomFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $room = $this->roomRepository->getById($roomFilterDto->room_id);
            $resource = $this->HandelRoomDetailResource(
                $room,
                $view,
                false
            );
            return DataSuccess(
                status: true,
                message: 'Room fetched successfully',
                data: $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function createRoom(RoomDTO $roomDTO): DataStatus
    {
        try {
            $roomDTO->created_by = $this->employee->id;
            $room = $this->roomRepository->create($roomDTO);
            $room->refresh();
            return DataSuccess(
                status: true,
                message: 'Room created successfully',
                data: new RoomResource($room)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }



    public function updateRoom(RoomDTO $roomDTO): DataStatus
    {
        try {
            $room = $this->roomRepository->update($roomDTO->room_id, $roomDTO);
            // $room->refresh();
            return DataSuccess(
                status: true,
                message: 'Room updated successfully',
                data: new RoomResource($room)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function deleteRoom(RoomFilterDTO $roomFilterDto): DataStatus
    {
        try {
            /** @var Room|null $room */
            $room = $this->roomRepository->getById($roomFilterDto->room_id);

            if (!$room) {
                return DataFailed(
                    status: false,
                    statusCode: 404,
                    message: 'Room not found'
                );
            }

            if ($room->children && $room->children->count() > 0) {
                return DataFailed(
                    status: false,
                    statusCode: 400,
                    message: 'Room has children'
                );
            }

            $this->roomRepository->delete($roomFilterDto->room_id);

            return DataSuccess(
                status: true,
                message: 'Room deleted successfully',
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    private function HandelRoomResource($rooms, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return RoomResource::collection($rooms);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return MobileResource::collection($rooms);
        } else {
            return RoomResource::collection($rooms);
        }
    }

    public function HandelRoomDetailResource($room, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return RoomDetailResource::make($room);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return MobileDetailsResource::make($room);
        } else {
            return RoomDetailResource::make($room);
        }
    }
}
