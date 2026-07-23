<?php

namespace App\Modules\Service\Http\Controllers\Api\Service;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Service\Application\UseCases\Service\ServiceUseCase;
use App\Modules\Service\Http\Requests\Service\FetchServiceRequest;

class ServiceController extends Controller
{
    public function __construct(private readonly ServiceUseCase $serviceUseCase)
    {
    }

    public function fetchServices(FetchServiceRequest $request)
    {
        return $this->serviceUseCase
            ->fetchServices($request->toDTO(), ViewTypeEnum::MOBILE->value)
            ->response();
    }
}
