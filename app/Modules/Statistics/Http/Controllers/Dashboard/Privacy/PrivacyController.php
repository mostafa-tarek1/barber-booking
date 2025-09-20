<?php

namespace App\Modules\Statistics\Http\Controllers\Dashboard\Privacy;

use App\Http\Controllers\Controller;
use App\Modules\Statistics\Application\UseCases\Privacy\PrivacyUseCase;
use App\Modules\Statistics\Http\Requests\Dashboard\Privacy\CreateOrUpdatePrivacyRequest;
use App\Modules\Statistics\Http\Requests\Dashboard\Privacy\FetchPrivacyRequest;



class PrivacyController extends Controller
{
    protected PrivacyUseCase $privacyUseCase;

    public function __construct(PrivacyUseCase $privacyUseCase)
    {
        $this->privacyUseCase = $privacyUseCase;
    }

    public function store_privacy(CreateOrUpdatePrivacyRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->toDTO();
        return $this->privacyUseCase->updateOrCreate(dto: $data)->response();
    }
    public function fetch_privacy(FetchPrivacyRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->toDTO();
        return $this->privacyUseCase->fetch(dto: $data)->response();
    }


}
