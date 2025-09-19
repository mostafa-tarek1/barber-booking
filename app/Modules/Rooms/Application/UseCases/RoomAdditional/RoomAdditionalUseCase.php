<?php

namespace App\Modules\Rooms\Application\UseCases\RoomAdditional;

use App\Modules\Base\Domain\Holders\AuthHolder;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Rooms\Application\DTOS\RoomAdditional\RoomAdditionalDTO;
use App\Modules\Rooms\Http\Resources\RoomAdditional\RoomAdditionalDetailResource;
use App\Modules\Rooms\Http\Resources\RoomAdditional\RoomAdditionalResource;
use App\Modules\Rooms\Infrastructure\Persistence\Repositories\RoomAdditional\RoomAdditionalRepository;
use App\Modules\Rooms\Application\DTOS\RoomAdditional\RoomAdditionalFilterDTO;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Rooms\Http\Resources\Api\RoomAdditional\RoomAdditionalResource as MobileRecourse;
use App\Modules\Rooms\Http\Resources\Api\RoomAdditional\RoomAdditionalDetailResource as MobileDetailsRecourse;


class RoomAdditionalUseCase
{

    protected $roomAdditionalRepository;
    protected $employee;
    public function __construct(RoomAdditionalRepository $roomAdditionalRepository)
    {
        $this->roomAdditionalRepository = $roomAdditionalRepository;
        $this->employee = AuthHolder::getInstance()->getAuth(AuthGurdTypeEnum::EMPLOYEE->value);
    }

    public function fetchRoomAdditionals(RoomAdditionalFilterDTO $roomAdditionalFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            // dd($roomAdditionalFilterDto);
            $roomAdditionals = $this->roomAdditionalRepository->filter(
                $roomAdditionalFilterDto,
                operator: 'like',
                translatableFields: ['title', 'description'],
                paginate: $roomAdditionalFilterDto->paginate,
                limit: $roomAdditionalFilterDto->limit
            );
            // dd($roomAdditionals);
            $resource = $this->HandelRoomAdditionalResource(
                $roomAdditionals,
                $view,
                $roomAdditionalFilterDto->paginate
            );
            return DataSuccess(
                status: true,
                message: 'RoomAdditionals fetched successfully',
                data: $roomAdditionalFilterDto->paginate ? $resource->response()->getData() : $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function fetchRoomAdditionalDetails(RoomAdditionalFilterDTO $roomAdditionalFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $roomAdditional = $this->roomAdditionalRepository->getById($roomAdditionalFilterDto->room_additional_id);
            $resource = $this->HandelRoomAdditionalDetailResource(
                $roomAdditional,
                $view,
                false
            );
            return DataSuccess(
                status: true,
                message: 'RoomAdditional fetched successfully',
                data: $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function createRoomAdditional(RoomAdditionalDTO $roomAdditionalDTO): DataStatus
    {
        // dd($roomAdditionalDTO);
        try {
            $roomAdditionalDTO->created_by = $this->employee->id;
            $roomAdditional = $this->roomAdditionalRepository->create($roomAdditionalDTO);
            $roomAdditional->refresh();
            return DataSuccess(
                status: true,
                message: 'RoomAdditional created successfully',
                data: new RoomAdditionalResource($roomAdditional)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }



    public function updateRoomAdditional(RoomAdditionalDTO $roomAdditionalDTO): DataStatus
    {
        try {
            $roomAdditionalDTO->updated_by = $this->employee->id;

            $roomAdditional = $this->roomAdditionalRepository->update(
                $roomAdditionalDTO->room_additional_id,
                $roomAdditionalDTO
            );

            $roomAdditional->refresh();

            return DataSuccess(
                status: true,
                message: 'RoomAdditional updated successfully',
                data: new RoomAdditionalResource($roomAdditional)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }



    public function deleteRoomAdditional(RoomAdditionalFilterDTO $roomAdditionalFilterDto): DataStatus
    {
        try {
            /** @var RoomAdditional|null $roomAdditional */
            $roomAdditional = $this->roomAdditionalRepository->getById($roomAdditionalFilterDto->room_additional_id);

            if (!$roomAdditional) {
                return DataFailed(
                    status: false,
                    statusCode: 404,
                    message: 'RoomAdditional not found'
                );
            }

            if ($roomAdditional->children && $roomAdditional->children->count() > 0) {
                return DataFailed(
                    status: false,
                    statusCode: 400,
                    message: 'RoomAdditional has children'
                );
            }

            $this->roomAdditionalRepository->delete($roomAdditionalFilterDto->room_additional_id);

            return DataSuccess(
                status: true,
                message: 'RoomAdditional deleted successfully',
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    private function HandelRoomAdditionalResource($roomAdditionals, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return RoomAdditionalResource::collection($roomAdditionals);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return MobileRecourse::collection($roomAdditionals);
        } else {
            return RoomAdditionalResource::collection($roomAdditionals);
        }
    }

    public function HandelRoomAdditionalDetailResource($roomAdditional, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return RoomAdditionalDetailResource::make($roomAdditional);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return MobileDetailsRecourse::make($roomAdditional);
        } else {
            return RoomAdditionalDetailResource::make($roomAdditional);
        }
    }
}
