<?php

namespace App\Modules\Statistics\Application\UseCases\Slider;

use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Base\Application\Response\DataSuccess;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Base\Domain\Holders\AuthHolder;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Statistics\Application\DTOS\Slider\SliderDTO;
use App\Modules\Statistics\Application\DTOS\Slider\SliderFilterDTO;
use App\Modules\Statistics\Http\Resources\Api\MobileSliderDetailResource;
use App\Modules\Statistics\Http\Resources\Api\MobileSliderResource;
use App\Modules\Statistics\Http\Resources\Dashboard\Slider\SliderDetailResource;
use App\Modules\Statistics\Http\Resources\Dashboard\Slider\SliderResource;
use App\Modules\Statistics\Infrastructure\Persistence\Repositories\Slider\SliderRepository;

class SliderUseCase
{
    protected $sliderRepository;
    protected $employee;

    public function __construct(SliderRepository $sliderRepository)
    {
        $this->sliderRepository = $sliderRepository;

        $this->employee = AuthHolder::getInstance()->getAuth(AuthGurdTypeEnum::EMPLOYEE->value);
    }

    public function fetchSliders(SliderFilterDTO $sliderFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $categories = $this->sliderRepository->filter(
                $sliderFilterDto,
                operator: 'like',
                translatableFields: ['title', 'description'],
                paginate: $sliderFilterDto->paginate,
                limit: $sliderFilterDto->limit
            );
            $resource = $this->HandelSliderResource(
                $categories,
                $view,
                $sliderFilterDto->paginate
            );
            return DataSuccess(
                status: true,
                message: 'slider fetched successfully',
                data: $sliderFilterDto->paginate ? $resource->response()->getData() : $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function fetchSliderDetails(SliderFilterDTO $sliderFilterDTO, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $slider = $this->sliderRepository->getById($sliderFilterDTO->slider_id);
            $resource = $this->HandelSliderDetailResource(
                $slider,
                $view,
                false
            );
            return DataSuccess(
                status: true,
                message: 'Slider fetched successfully',
                data: $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function createSlider(SliderDTO $sliderDTO): DataStatus
    {
        try {
            // $sliderDTO->created_by = $this->employee->id;
            // dd($sliderDTO);
            $slider = $this->sliderRepository->create($sliderDTO);

            $slider->refresh();
            return DataSuccess(
                status: true,
                message: 'slider created successfully',
                data: new SliderResource($slider)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function updateSlider(SliderDTO $sliderDTO): DataStatus
    {
        try {
            // $sliderDTO->updated_by = $this->employee->id;
            $slider = $this->sliderRepository->update($sliderDTO->slider_id, $sliderDTO);
            $sliderDTO->slider_id = $slider->id;
            $slider->refresh();
            return DataSuccess(
                status: true,
                message: 'Slider updated successfully',
                data: new SliderResource($slider)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function deleteSlider(SliderFilterDTO $sliderFilterDto): DataStatus
    {
        try {
            $this->sliderRepository->delete($sliderFilterDto->slider_id);

            return DataSuccess(
                status: true,
                message: 'Slider deleted successfully',
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }




    private function HandelSliderResource($slider, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return SliderResource::collection($slider);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return MobileSliderResource::collection($slider);
        } else {
            return SliderResource::collection($slider);
        }
    }

    public function HandelSliderDetailResource($slider, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return SliderDetailResource::make($slider);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return MobileSliderResource::collection($slider);
        } else {
            return SliderDetailResource::collection($slider);
        }
    }
}
