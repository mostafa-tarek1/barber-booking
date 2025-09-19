<?php

namespace App\Modules\Rooms\Application\UseCases\RoomLocation;

use App\Modules\Base\Domain\Holders\AuthHolder;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Rooms\Application\DTOS\RoomLocation\RoomLocationDTO;
use App\Modules\Rooms\Application\DTOS\RoomLocation\RoomLocationFilterDTO;
use App\Modules\Rooms\Http\Resources\Api\RoomLocation\RoomLocationDetailResource as MobileDetailsRecourse;
use App\Modules\Rooms\Http\Resources\RoomLocation\RoomLocationDetailResource;
use App\Modules\Rooms\Http\Resources\RoomLocation\RoomLocationResource;
use App\Modules\Rooms\Http\Resources\Api\RoomLocation\RoomLocationResource as MobileRecourse;
use App\Modules\Rooms\Infrastructure\Persistence\Models\RoomLocation\RoomLocation;
use App\Modules\Rooms\Infrastructure\Persistence\Repositories\RoomLocation\RoomLocationRepository;
class RoomLocationUseCase
{

    protected $roomLocationRepository;
    protected $employee;
    public function __construct(RoomLocationRepository $roomLocationRepository)
    {
        $this->roomLocationRepository = $roomLocationRepository;
        $this->employee = AuthHolder::getInstance()->getAuth(AuthGurdTypeEnum::EMPLOYEE->value);
    }

    public function fetchRoomLocations(RoomLocationFilterDTO $roomLocationFilterDTO, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            // dd($roomLocationFilterDTO);
            $roomLocation = $this->roomLocationRepository->filter(
                $roomLocationFilterDTO,
                operator: 'like',
                translatableFields: ['title', 'description'],
                paginate: $roomLocationFilterDTO->paginate,
                limit: $roomLocationFilterDTO->limit
            );
            // dd($roomLocation);
            $resource = $this->HandelRoomResource(
                $roomLocation,
                $view,
                $roomLocationFilterDTO->paginate
            );
            return DataSuccess(
                status: true,
                message: 'Locations fetched successfully',
                data: $roomLocationFilterDTO->paginate ? $resource->response()->getData() : $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function fetchRoomLocationDetails(RoomLocationFilterDTO $roomLocationFilterDTO, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $roomLocation = $this->roomLocationRepository->getById($roomLocationFilterDTO->room_location_id);
            $resource = $this->HandelRoomDetailResource(
                $roomLocation,
                $view,
                false
            );
            return DataSuccess(
                status: true,
                message: 'Location fetched successfully',
                data: $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function createRoomLocation(RoomLocationDTO $roomLocationDTO): DataStatus
    {
        try {
            $roomLocationDTO->created_by = $this->employee->id;
            // dd($roomLocationDTO->created_by);
            $roomLocation = $this->roomLocationRepository->create($roomLocationDTO);
            $roomLocation->refresh();
            return DataSuccess(
                status: true,
                message: 'Room Location created successfully',
                data: new RoomLocationResource($roomLocation)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }



    public function updateRoomLocation(RoomLocationDTO $roomLocationDTO): DataStatus
    {
        try {
            $roomLocation = $this->roomLocationRepository->update($roomLocationDTO->room_location_id, $roomLocationDTO);
            return DataSuccess(
                status: true,
                message: 'Room location updated successfully',
                data: new RoomLocationResource($roomLocation)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function deleteRoomLocation(RoomLocationFilterDTO $roomLocationFilterDTO): DataStatus
    {
        try {
            /** @var RoomLocation|null $roomLocation */
            $roomLocation = $this->roomLocationRepository->getById($roomLocationFilterDTO->room_location_id);

            if (!$roomLocation) {
                return DataFailed(
                    status: false,
                    statusCode: 404,
                    message: 'Room location not found'
                );
            }

            if ($roomLocation->children && $roomLocation->children->count() > 0) {
                return DataFailed(
                    status: false,
                    statusCode: 400,
                    message: 'Room location has children'
                );
            }

            $this->roomLocationRepository->delete($roomLocationFilterDTO->room_location_id);

            return DataSuccess(
                status: true,
                message: 'Room location deleted successfully',
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    private function HandelRoomResource($roomLocations, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return RoomLocationResource::collection($roomLocations);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return MobileRecourse::collection($roomLocations);
        }  else {
            return RoomLocationResource::collection($roomLocations);
        }
    }

    public function HandelRoomDetailResource($room, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return RoomLocationDetailResource::make($room);
        }elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return MobileDetailsRecourse::make($room);
        } else {
            return RoomLocationDetailResource::make($room);
        }
    }
}
