<?php

namespace App\Modules\Service\Application\UseCases\Service;

use App\Modules\Base\Domain\Holders\AuthHolder;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Service\Application\DTOS\Service\ServiceDTO;
use App\Modules\Service\Infrastructure\Persistence\Repositories\Service\ServiceRepository;
use App\Modules\Service\Application\DTOS\Service\ServiceFilterDTO;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Service\Http\Resources\Api\ServiceResource as ApiServiceResource;
use App\Modules\Service\Http\Resources\Api\ServiceDetailResource as ApiServiceDetailResource;
use App\Modules\Service\Http\Resources\Dashboard\ServiceResource;
use App\Modules\Service\Http\Resources\Dashboard\ServiceDetailResource;
use Illuminate\Support\Facades\Log;

class ServiceUseCase
{

    protected $serviceRepository;
    protected $employee;
    public function __construct(ServiceRepository $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
        $this->employee = AuthHolder::getInstance()->getAuth(AuthGurdTypeEnum::EMPLOYEE->value);
    }

    public function fetchServices(ServiceFilterDTO $serviceFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            // dd($serviceFilterDto);
            // simplified: repository filter without categories/hashtags
            $services = $this->serviceRepository->filter(
                $serviceFilterDto,
                operator: 'like',
                translatableFields: ['title', 'description'],
                paginate: $serviceFilterDto->paginate,
                limit: $serviceFilterDto->limit
            );
            // dd($services);
            if (is_null($services)) {
                $services = collect();
            }
            $resource = $this->HandelServiceResource(
                $services,
                $view,
                $serviceFilterDto->paginate
            );
            return DataSuccess(
                status: true,
                message: 'Services fetched successfully',
                data: $serviceFilterDto->paginate ? $resource->response()->getData() : $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function fetchServiceDetails(ServiceFilterDTO $serviceFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            if (isset($serviceFilterDto->service_id)) {
                $service = $this->serviceRepository->getById($serviceFilterDto->service_id);
            } else {
                throw new \Exception('service_id must be provided');
            }

            $resource = $this->HandelServiceDetailResource(
                $service,
                $view,
                false
            );
            return DataSuccess(
                status: true,
                message: 'Service fetched successfully',
                data: $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function createService(ServiceDTO $serviceDTO): DataStatus
    {
        try {
            $serviceDTO->created_by = $this->employee->id;
            $service = $this->serviceRepository->create($serviceDTO);
            $service->refresh();
            return DataSuccess(
                status: true,
                message: 'Service created successfully',
                data: new ServiceResource($service)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }



    public function updateService(ServiceDTO $serviceDTO): DataStatus
    {
        try {
            // The DTO does not carry service_id in this project; expect caller to provide it via filter DTO
            // We'll attempt to update by an id property if present inside the DTO, otherwise throw
            // determine id from possible DTO properties without direct property access
            $dtoVars = get_object_vars($serviceDTO);
            $id = $dtoVars['service_id'] ?? $dtoVars['id'] ?? $dtoVars['serviceId'] ?? null;

            if (empty($id)) {
                throw new \Exception('service id is required to update');
            }
            $service = $this->serviceRepository->update($id, $serviceDTO);

            return DataSuccess(
                status: true,
                message: 'Service updated successfully',
                data: new ServiceResource($service)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function deleteService(ServiceFilterDTO $serviceFilterDto): DataStatus
    {
        try {
            $service = $this->serviceRepository->getById($serviceFilterDto->service_id);

            if (!$service) {
                return DataFailed(
                    status: false,
                    statusCode: 404,
                    message: 'Service not found'
                );
            }

            if ($service->children && $service->children->count() > 0) {
                return DataFailed(
                    status: false,
                    statusCode: 400,
                    message: 'Service has children'
                );
            }

            $this->serviceRepository->delete($serviceFilterDto->service_id);

            return DataSuccess(
                status: true,
                message: 'Service deleted successfully',
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    private function HandelServiceResource($services, $viewType, $paginate)
    {
        $services = $services ?? collect();
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return ServiceResource::collection($services);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return ApiServiceResource::collection($services);
        } else {
            return ServiceResource::collection($services);
        }
    }

    public function HandelServiceDetailResource($service, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return ServiceDetailResource::make($service);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return ApiServiceDetailResource::make($service);
        } else {
            return ServiceDetailResource::make($service);
        }
    }
}
