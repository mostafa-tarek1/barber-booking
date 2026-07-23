<?php

namespace App\Modules\Statistics\Http\Controllers\Dashboard\Setting;

use App\Http\Controllers\Controller;
use App\Modules\Statistics\Application\UseCases\Setting\SettingUseCase;
use App\Modules\Statistics\Http\Requests\Dashboard\Setting\CreateOrUpdateRequest;
use App\Modules\Statistics\Http\Requests\Dashboard\Setting\FetchRequest;
use Illuminate\Support\Facades\Auth;

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
        abort_unless(
            in_array(Auth::user()?->employee_type, ['admin', 'super_admin'], true),
            403,
            'هذه العملية متاحة للأدمن فقط.'
        );
        $data = $request->toDTO();

        return $this->settingUseCase->updateOrCreate(dto: $data)->response();

    }
}
