<?php

namespace App\Modules\Statistics\Http\Controllers\Dashboard\About;

use App\Http\Controllers\Controller;
use App\Modules\Statistics\Application\UseCases\About\AboutUseCase;
use App\Modules\Statistics\Http\Requests\Dashboard\About\CreateOrUpdateAboutRequest;
use App\Modules\Statistics\Http\Requests\Dashboard\About\FetchAboutsRequest;

class AboutController extends Controller
{
    protected $aboutUseCase;

    public function __construct(AboutUseCase $aboutUseCase)
    {
        $this->aboutUseCase = $aboutUseCase;
    }

    public function createOrUpdate(CreateOrUpdateAboutRequest $request)
    {
        return $this->aboutUseCase->createOrUpdateAbout($request->toDTO())->response();
    }

        public function fetchAbouts(FetchAboutsRequest $request)
    {
        return $this->aboutUseCase->fetchAbouts($request->toDTO())->response();
    }
}
