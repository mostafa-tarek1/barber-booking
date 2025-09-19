<?php

namespace App\Modules\Statistics\Http\Controllers\Website\AboutUs;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Statistics\Application\UseCases\AboutUs\AboutUsUseCase;
use App\Modules\Statistics\Http\Requests\Dashboard\AboutUs\FetchAboutUsRequest;

class AboutUsController extends Controller
{
    protected $aboutUsUseCase;

    public function __construct(AboutUsUseCase $aboutUsUseCase)
    {
        $this->aboutUsUseCase = $aboutUsUseCase;
    }

    public function fetchAboutUs(FetchAboutUsRequest $request)
    {
        return $this->aboutUsUseCase->fetchAboutUs($request->toDTO(), ViewTypeEnum::MOBILE->value)->response();
    }
}
