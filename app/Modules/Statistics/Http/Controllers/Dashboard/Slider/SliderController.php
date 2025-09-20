<?php

namespace App\Modules\Statistics\Http\Controllers\Dashboard\Slider;

use App\Http\Controllers\Controller;
use App\Modules\Statistics\Application\UseCases\Slider\SliderUseCase;
use App\Modules\Statistics\Http\Requests\Dashboard\Slider\SliderIdRequest;
use App\Modules\Statistics\Http\Requests\Dashboard\Slider\CreateSliderRequest;
use App\Modules\Statistics\Http\Requests\Dashboard\Slider\FetchSliderRequest;
use App\Modules\Statistics\Http\Requests\Dashboard\Slider\UpdateSliderRequest;
class SliderController extends Controller
{
    protected $sliderUseCase;

    public function __construct(SliderUseCase $sliderUseCase)
    {
        $this->sliderUseCase = $sliderUseCase;
    }

    public function fetchSliders(FetchSliderRequest $request)
    {
        return $this->sliderUseCase->fetchSliders($request->toDTO())->response();
    }

    public function fetchSliderDetails(SliderIdRequest $request)
    {
        return $this->sliderUseCase->fetchSliderDetails($request->toDTO())->response();
    }

    public function createSlider(CreateSliderRequest $request)
    {
        return $this->sliderUseCase->createSlider($request->toDTO())->response();
    }

    public function updateSlider(UpdateSliderRequest $request)
    {
        return $this->sliderUseCase->updateSlider($request->toDTO())->response();
    }

    public function deleteSlider(SliderIdRequest $request)
    {
        return $this->sliderUseCase->deleteSlider($request->toDTO())->response();
    }
}
