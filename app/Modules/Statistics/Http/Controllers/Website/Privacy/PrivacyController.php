<?php

namespace App\Modules\Statistics\Http\Controllers\Website\Privacy;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Statistics\Application\UseCases\Privacy\PrivacyUseCase;
use App\Modules\Statistics\Http\Requests\Dashboard\Privacy\FetchPrivacyRequest;



class PrivacyController extends Controller
{
    protected PrivacyUseCase $privacyUseCase;

    public function __construct(PrivacyUseCase $privacyUseCase)
    {
        $this->privacyUseCase = $privacyUseCase;
    }

    public function fetch_privacy(FetchPrivacyRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->toDTO();
        return $this->privacyUseCase->fetch(dto: $data , type: ViewTypeEnum::MOBILE->value)->response();
    }
}
