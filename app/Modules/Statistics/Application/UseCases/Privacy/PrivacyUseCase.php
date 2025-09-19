<?php

namespace App\Modules\Statistics\Application\UseCases\Privacy;


use App\Modules\Base\Application\Response\DataSuccess;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Statistics\Http\Resources\Dashboard\Privacy\PrivacyDetailResource;
use App\Modules\Statistics\Http\Resources\Dashboard\Privacy\PrivacyResource;
use App\Modules\Statistics\Http\Resources\Info\AcceptedInfoResource;
use App\Modules\Statistics\Http\Resources\Privacy\AcceptedPrivacyResource;
use App\Modules\Statistics\Infrastructure\Persistence\Repositories\Privacy\PrivacyRepository;

class PrivacyUseCase
{

    protected PrivacyRepository $privacyRepository;
    public function __construct(PrivacyRepository $privacyRepository)
    {
        $this->privacyRepository = $privacyRepository;
    }

    public function updateOrCreate($dto) : DataSuccess
    {
        $privacy = $this->privacyRepository->updateOrCreate($dto);
        // dd($privacy->description);
        return new DataSuccess(
            data: new PrivacyResource($privacy),
            message: 'Privacy updated successfully'
        );
    }
    public function fetch($dto,$type = ViewTypeEnum::DASHBOARD->value): DataSuccess
    {
        $privacy = $this->privacyRepository->getFirst();

        if ($type == ViewTypeEnum::DASHBOARD->value) {
            return new DataSuccess(
                data: PrivacyDetailResource::make($privacy),
                message: 'privacy fetched successfully'
            );
        } else {
            return new DataSuccess(
                data: getTranslation('description', request()->header('Accept-Language'), $privacy),
                message: 'privacy fetched successfully'
            );
        }

        return new DataSuccess(
            data: $resource,
            message: 'privacy fetched successfully'
        );
    }
}
