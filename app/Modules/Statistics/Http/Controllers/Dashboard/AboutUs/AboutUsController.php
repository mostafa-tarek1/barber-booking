<?php

namespace App\Modules\Statistics\Http\Controllers\Dashboard\AboutUs;

use App\Http\Controllers\Controller;
use App\Modules\Statistics\Application\UseCases\AboutUs\AboutUsUseCase;
use App\Modules\Statistics\Http\Requests\Dashboard\AboutUs\AboutUsImageIdRequest;
use App\Modules\Statistics\Http\Requests\Dashboard\AboutUs\CreateOrUpdateAboutUsRequest;
use App\Modules\Statistics\Http\Requests\Dashboard\AboutUs\FetchAboutUsRequest;

class AboutUsController extends Controller
{
    protected $aboutUsUseCase;

    public function __construct(AboutUsUseCase $aboutUsUseCase)
    {
        $this->aboutUsUseCase = $aboutUsUseCase;
    }

    public function createOrUpdate(CreateOrUpdateAboutUsRequest $request)
    {
        return $this->aboutUsUseCase->createOrUpdateAboutUs($request->toDTO())->response();
    }

    public function fetchAboutUs(FetchAboutUsRequest $request)
    {
        return $this->aboutUsUseCase->fetchAboutUs($request->toDTO())->response();
    }

    public function deleteAboutUsImage(AboutUsImageIdRequest $request)
    {
        return $this->aboutUsUseCase->deleteAboutUsImage($request->toDTO())->response();
    }
}
