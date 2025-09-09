<?php

namespace App\Modules\Statistics\Application\UseCases\ContactUs;

use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Base\Application\Response\DataSuccess;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Base\Domain\Holders\AuthHolder;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Statistics\Application\DTOS\ContactUs\ContactUsDTO;
use App\Modules\Statistics\Application\DTOS\ContactUs\ContactUsFilterDTO;
use App\Modules\Statistics\Http\Resources\Dashboard\ContactUs\ContactUsDetailResource;
use App\Modules\Statistics\Http\Resources\Dashboard\ContactUs\ContactUsResource;
use App\Modules\Statistics\Infrastructure\Persistence\Repositories\ContactUs\ContactUsRepository;

class ContactUsUseCase
{
    protected $contactUsRepository;
    protected $employee;

    public function __construct(ContactUsRepository $contactUsRepository)
    {
        $this->contactUsRepository = $contactUsRepository;

        $this->employee = AuthHolder::getInstance()->getAuth(AuthGurdTypeEnum::EMPLOYEE->value);
    }

    public function fetchContactUs(ContactUsFilterDTO $contactUsFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $categories = $this->contactUsRepository->filter(
                $contactUsFilterDto,
                operator: 'like',
                translatableFields: ['title', 'description'],
                paginate: $contactUsFilterDto->paginate,
                limit: $contactUsFilterDto->limit
            );
            $resource = $this->HandelContactUsResource(
                $categories,
                $view,
                $contactUsFilterDto->paginate
            );
            return DataSuccess(
                status: true,
                message: 'contact us fetched successfully',
                data: $contactUsFilterDto->paginate ? $resource->response()->getData() : $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function fetchContactUsDetails(ContactUsFilterDTO $contactUsFilterDTO, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $contactUs = $this->contactUsRepository->getById($contactUsFilterDTO->contact_us_id);
            $resource = $this->HandelContactUsDetailResource(
                $contactUs,
                $view,
                false
            );
            return DataSuccess(
                status: true,
                message: 'ContactUs fetched successfully',
                data: $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function createContactUs(ContactUsDTO $contactUsDTO): DataStatus
    {
        try {
            $contactUs = $this->contactUsRepository->create($contactUsDTO);
            $contactUs->refresh();
            return DataSuccess(
                status: true,
                message: 'contactUs created successfully',
                data: new ContactUsResource($contactUs)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function deleteContactUs(ContactUsFilterDTO $contactUsFilterDto): DataStatus
    {
        try {
            $this->contactUsRepository->delete($contactUsFilterDto->contact_us_id);

            return DataSuccess(
                status: true,
                message: 'ContactUs deleted successfully',
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }




    private function HandelContactUsResource($contactUs, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return ContactUsResource::collection($contactUs);
        } else {
            return ContactUsResource::collection($contactUs);
        }
    }

    public function HandelContactUsDetailResource($contactUs, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return ContactUsDetailResource::make($contactUs);
        } else {
            return ContactUsDetailResource::collection($contactUs);
        }
    }
}
