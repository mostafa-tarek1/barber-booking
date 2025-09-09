<?php

namespace App\Modules\Statistics\Http\Controllers\Website\Slider;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Statistics\Application\UseCases\Slider\SliderUseCase;
use App\Modules\Statistics\Http\Requests\Dashboard\Slider\FetchSliderRequest;

class SliderController extends Controller
{
    protected $sliderUseCase;

    public function __construct(SliderUseCase $sliderUseCase)
    {
        $this->sliderUseCase = $sliderUseCase;
    }

    public function fetchSliders(FetchSliderRequest $request)
    {
        return $this->sliderUseCase->fetchSliders($request->toDTO() , ViewTypeEnum::MOBILE->value)->response();
    }


}
