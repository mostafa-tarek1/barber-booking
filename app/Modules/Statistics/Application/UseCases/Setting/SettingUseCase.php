<?php

namespace App\Modules\Statistics\Application\UseCases\Setting;


use App\Modules\Base\Application\Response\DataSuccess;
use App\Modules\Category\Application\Enums\View\ViewTypeEnum;
use App\Modules\Statistics\Http\Resources\Api\Setting\SettingResource as MobileSettingResource;
use App\Modules\Statistics\Http\Resources\Dashboard\Setting\SettingResource;
use App\Modules\Statistics\Infrastructure\Persistence\Repositories\Setting\SettingRepository;

class SettingUseCase
{

    protected SettingRepository $settingRepository;
    public function __construct(SettingRepository $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }
    public function fetch($dto, $type = ViewTypeEnum::DASHBOARD->value): DataSuccess
    {
        $data = $dto->toArray();
        $setting = $this->settingRepository->getFirst();

        if ($type == ViewTypeEnum::DASHBOARD->value) {
            return new DataSuccess(
                data: SettingResource::make($setting),
                message: 'setting fetched successfully'
            );
        } else {
            return new DataSuccess(
               data: MobileSettingResource::make($setting),
                message: 'setting fetched successfully'
            );
        }

        return new DataSuccess(
            data: $resource,
            message: 'Settings fetched successfully'
        );
    }
    public function updateOrCreate($dto): DataSuccess
    {
        $data = $this->settingRepository->updateOrCreate($dto);
        return new DataSuccess(
            data: new SettingResource($data),
            message: 'Setting updated successfully'

        );
    }
}
