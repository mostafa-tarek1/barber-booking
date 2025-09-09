<?php

namespace App\Modules\Statistics\Http\Controllers\Dashboard\Setting;

use App\Http\Controllers\Controller;
use App\Modules\Statistics\Application\UseCases\Setting\SettingUseCase;
use App\Modules\Statistics\Http\Requests\Dashboard\Setting\CreateOrUpdateRequest;
use App\Modules\Statistics\Http\Requests\Dashboard\Setting\FetchRequest;


class SettingController extends Controller
{
    protected SettingUseCase $settingUseCase;

    public function __construct(SettingUseCase $settingUseCase)
    {
        $this->settingUseCase = $settingUseCase;
    }

    public function fetchSettings(FetchRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->toDTO();
        return $this->settingUseCase->fetch(dto: $data)->response();
    }

    public function createSettings(CreateOrUpdateRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->toDTO();
        return $this->settingUseCase->updateOrCreate(dto: $data)->response();

    }



}
