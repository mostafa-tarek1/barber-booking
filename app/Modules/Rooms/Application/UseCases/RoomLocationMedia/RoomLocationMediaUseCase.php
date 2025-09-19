<?php

namespace App\Modules\Rooms\Application\UseCases\RoomLocationMedia;

use App\Modules\Base\Domain\Holders\AuthHolder;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Rooms\Application\DTOS\RoomLocationMedia\RoomLocationMediaDTO;
use App\Modules\Rooms\Application\DTOS\RoomLocationMedia\RoomLocationMediaFilterDTO;
use App\Modules\Rooms\Http\Resources\RoomLocationMedia\RoomLocationMediaDetailResource;
use App\Modules\Rooms\Http\Resources\RoomLocationMedia\RoomLocationMediaResource;
use App\Modules\Rooms\Infrastructure\Persistence\Models\RoomLocationMedia\RoomLocationMedia;
use App\Modules\Rooms\Infrastructure\Persistence\Repositories\RoomLocationMedia\RoomLocationMediaRepository;
use App\Modules\Rooms\Http\Resources\Api\RoomLocationMedia\RoomLocationMediaDetailResource as MobileDetailsResource;
use App\Modules\Rooms\Http\Resources\Api\RoomLocationMedia\RoomLocationMediaResource as MobileRecourse;


class RoomLocationMediaUseCase
{

    protected $roomLocationMediaRepository;
    protected $employee;
    public function __construct(RoomLocationMediaRepository $roomLocationMediaRepository)
    {
        $this->roomLocationMediaRepository = $roomLocationMediaRepository;
        $this->employee = AuthHolder::getInstance()->getAuth(AuthGurdTypeEnum::EMPLOYEE->value);
    }

    public function fetchRoomLocationMedias(RoomLocationMediaFilterDTO $roomLocationMediaFilterDTO, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            // dd($roomLocationMediaFilterDTO);
            $roomLocationMedia = $this->roomLocationMediaRepository->filter(
                $roomLocationMediaFilterDTO,
                operator: 'like',
                translatableFields: ['title', 'description'],
                paginate: $roomLocationMediaFilterDTO->paginate,
                limit: $roomLocationMediaFilterDTO->limit
            );
            // dd($roomLocationMedia);
            $resource = $this->HandelRoomResource(
                $roomLocationMedia,
                $view,
                $roomLocationMediaFilterDTO->paginate
            );
            return DataSuccess(
                status: true,
                message: 'LocationMedias fetched successfully',
                data: $roomLocationMediaFilterDTO->paginate ? $resource->response()->getData() : $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function fetchRoomLocationMediaDetails(RoomLocationMediaFilterDTO $roomLocationMediaFilterDTO, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $roomLocationMedia = $this->roomLocationMediaRepository->getById($roomLocationMediaFilterDTO->room_location_media_id);
            $resource = $this->HandelRoomDetailResource(
                $roomLocationMedia,
                $view,
                false
            );
            return DataSuccess(
                status: true,
                message: 'LocationMedia fetched successfully',
                data: $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function createRoomLocationMedia(RoomLocationMediaDTO $roomLocationMediaDTO): DataStatus
    {
        try {
            $roomLocationMediaDTO->created_by = $this->employee->id;
            
            // Ensure file_type is set and is an integer
            if (empty($roomLocationMediaDTO->file_type) && !empty($roomLocationMediaDTO->file)) {
                $extension = strtolower(pathinfo($roomLocationMediaDTO->file, PATHINFO_EXTENSION));
                $roomLocationMediaDTO->file_type = match($extension) {
                    'jpg', 'jpeg', 'png', 'gif', 'webp' => RoomLocationMediaFileTypeEnum::IMAGE->value,
                    'mp4', 'webm', 'mov', 'avi', 'mkv' => RoomLocationMediaFileTypeEnum::VIDEO->value,
                    'mp3', 'wav', 'ogg' => RoomLocationMediaFileTypeEnum::AUDIO->value,
                    default => RoomLocationMediaFileTypeEnum::OTHER->value,
                };
            }
            
            // Ensure file_type is an integer
            if (!is_null($roomLocationMediaDTO->file_type)) {
                $roomLocationMediaDTO->file_type = (int) $roomLocationMediaDTO->file_type;
            }
            
            $roomLocationMedia = $this->roomLocationMediaRepository->create($roomLocationMediaDTO);
            $roomLocationMedia->refresh();
            
            return DataSuccess(
                status: true,
                message: 'Room LocationMedia created successfully',
                data: new RoomLocationMediaResource($roomLocationMedia)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }



    public function updateRoomLocationMedia(RoomLocationMediaDTO $roomLocationMediaDTO): DataStatus
    {
        try {
            $roomLocationMedia = $this->roomLocationMediaRepository->update($roomLocationMediaDTO->room_location_media_id, $roomLocationMediaDTO);
            return DataSuccess(
                status: true,
                message: 'Room updated successfully',
                data: new RoomLocationMediaResource($roomLocationMedia)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function deleteRoomLocationMedia(RoomLocationMediaFilterDTO $roomLocationMediaFilterDTO): DataStatus
    {
        try {
            /** @var RoomLocationMedia|null $roomLocationMedia */
            $roomLocationMedia = $this->roomLocationMediaRepository->getById($roomLocationMediaFilterDTO->room_location_media_id);

            if (!$roomLocationMedia) {
                return DataFailed(
                    status: false,
                    statusCode: 404,
                    message: 'Room location not found'
                );
            }

            if ($roomLocationMedia->children && $roomLocationMedia->children->count() > 0) {
                return DataFailed(
                    status: false,
                    statusCode: 400,
                    message: 'Room location has children'
                );
            }

            $this->roomLocationMediaRepository->delete($roomLocationMediaFilterDTO->room_location_media_id);

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

    private function HandelRoomResource($roomLocationMedias, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return RoomLocationMediaResource::collection($roomLocationMedias);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return MobileRecourse::collection($roomLocationMedias);
        }  else {
            return RoomLocationMediaResource::collection($roomLocationMedias);
        }
    }

    public function HandelRoomDetailResource($room, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return RoomLocationMediaDetailResource::make($room);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return MobileDetailsResource::make($room);
        } else {
            return RoomLocationMediaDetailResource::make($room);
        }
    }
}
