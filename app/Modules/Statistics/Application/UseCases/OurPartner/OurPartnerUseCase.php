<?php

namespace App\Modules\Statistics\Application\UseCases\OurPartner;

use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Base\Application\Response\DataSuccess;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Base\Domain\Holders\AuthHolder;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Statistics\Application\DTOS\OurPartner\OurPartnerDTO;
use App\Modules\Statistics\Application\DTOS\OurPartner\OurPartnerFilterDTO;
use App\Modules\Statistics\Http\Resources\Api\MobileOurPartnerDetailResource;
use App\Modules\Statistics\Http\Resources\Api\MobileOurPartnerResource;
use App\Modules\Statistics\Http\Resources\Dashboard\OurPartner\OurPartnerDetailResource;
use App\Modules\Statistics\Http\Resources\Dashboard\OurPartner\OurPartnerResource;
use App\Modules\Statistics\Infrastructure\Persistence\Repositories\OurPartner\OurPartnerRepository;

class OurPartnerUseCase
{
    protected $ourPartnerRepository;
    protected $employee;

    public function __construct(OurPartnerRepository $ourPartnerRepository)
    {
        $this->ourPartnerRepository = $ourPartnerRepository;

        $this->employee = AuthHolder::getInstance()->getAuth(AuthGurdTypeEnum::EMPLOYEE->value);
    }

    public function fetchOurPartners(OurPartnerFilterDTO $ourPartnerFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $categories = $this->ourPartnerRepository->filter(
                $ourPartnerFilterDto,
                operator: 'like',
                translatableFields: ['title', 'description'],
                paginate: $ourPartnerFilterDto->paginate,
                limit: $ourPartnerFilterDto->limit
            );
            $resource = $this->HandelOurPartnerResource(
                $categories,
                $view,
                $ourPartnerFilterDto->paginate
            );
            return DataSuccess(
                status: true,
                message: 'Our partners fetched successfully',
                data: $ourPartnerFilterDto->paginate ? $resource->response()->getData() : $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function fetchOurPartnerDetails(OurPartnerFilterDTO $ourPartnerFilterDTO, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $ourPartner = $this->ourPartnerRepository->getById($ourPartnerFilterDTO->our_partner_id);
            $resource = $this->HandelOurPartnerDetailResource(
                $ourPartner,
                $view,
                false
            );
            return DataSuccess(
                status: true,
                message: 'OurPartner fetched successfully',
                data: $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function createOurPartner(OurPartnerDTO $ourPartnerDTO): DataStatus
    {
        try {
            $ourPartnerDTO->created_by = $this->employee->id;
            $ourPartner = $this->ourPartnerRepository->create($ourPartnerDTO);

            $ourPartner->refresh();
            return DataSuccess(
                status: true,
                message: 'ourPartner created successfully',
                data: new OurPartnerResource($ourPartner)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function updateOurPartner(OurPartnerDTO $ourPartnerDTO): DataStatus
    {
        try {
            $ourPartnerDTO->updated_by = $this->employee->id;
            $ourPartner = $this->ourPartnerRepository->update($ourPartnerDTO->our_partner_id, $ourPartnerDTO);
            $ourPartnerDTO->our_partner_id = $ourPartner->id;
            $ourPartner->refresh();
            return DataSuccess(
                status: true,
                message: 'OurPartner updated successfully',
                data: new OurPartnerResource($ourPartner)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function deleteOurPartner(OurPartnerFilterDTO $ourPartnerFilterDto): DataStatus
    {
        try {
            $this->ourPartnerRepository->delete($ourPartnerFilterDto->our_partner_id);

            return DataSuccess(
                status: true,
                message: 'OurPartner deleted successfully',
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }




    private function HandelOurPartnerResource($ourPartner, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return OurPartnerResource::collection($ourPartner);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return MobileOurPartnerResource::collection($ourPartner);
        } else {
            return OurPartnerResource::collection($ourPartner);
        }
    }

    public function HandelOurPartnerDetailResource($ourPartner, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return OurPartnerDetailResource::make($ourPartner);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return MobileOurPartnerDetailResource::collection($ourPartner);
        } else {
            return OurPartnerDetailResource::collection($ourPartner);
        }
    }
}
