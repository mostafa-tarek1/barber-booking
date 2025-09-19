<?php

namespace App\Modules\Statistics\Http\Controllers\Website\About;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Statistics\Application\UseCases\About\AboutUseCase;
use App\Modules\Statistics\Http\Requests\Dashboard\About\FetchAboutsRequest;

class AboutController extends Controller
{
    protected $aboutUseCase;

    public function __construct(AboutUseCase $aboutUseCase)
    {
        $this->aboutUseCase = $aboutUseCase;
    }

    public function fetchAbouts(FetchAboutsRequest $request)
    {
        return $this->aboutUseCase->fetchAbouts($request->toDTO(), ViewTypeEnum::MOBILE->value)->response();
    }

    public function fetchContactInfos(FetchAboutsRequest $request)
    {
        return $this->aboutUseCase->fetchContactInfos($request->toDTO(), ViewTypeEnum::MOBILE->value)->response();
    }
}
