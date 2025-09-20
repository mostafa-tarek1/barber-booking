<?php

namespace App\Modules\Statistics\Application\UseCases\AboutUs;

use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Base\Domain\Holders\AuthHolder;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Statistics\Application\DTOS\AboutUs\AboutUsDTO;
use App\Modules\Statistics\Application\DTOS\AboutUs\AboutUsFilterDTO;
use App\Modules\Statistics\Application\DTOS\AboutUs\AboutUsImageDTO;
use App\Modules\Statistics\Application\DTOS\AboutUs\AboutUsStatisticsDTO;
use App\Modules\Statistics\Http\Resources\Dashboard\AboutUs\AboutUsDetailResource;
use App\Modules\Statistics\Infrastructure\Persistence\Repositories\AboutUs\AboutUsRepository;
use App\Modules\Statistics\Infrastructure\Persistence\Repositories\AboutUsImage\AboutUsImageRepository;
use App\Modules\Statistics\Infrastructure\Persistence\Repositories\AboutUsStatistics\AboutUsStatisticRepository;
use Illuminate\Support\Facades\DB;

class AboutUsUseCase
{
    protected $aboutUsRepository;
    protected $aboutUsStatisticRepository;
    protected $aboutUsImageRepository;
    protected $employee;

    public function __construct(
        AboutUsRepository $aboutUsRepository,
        AboutUsStatisticRepository $aboutUsStatisticRepository,
        AboutUsImageRepository $aboutUsImageRepository
    ) {
        $this->aboutUsRepository = $aboutUsRepository;
        $this->aboutUsStatisticRepository = $aboutUsStatisticRepository;
        $this->aboutUsImageRepository = $aboutUsImageRepository;
        $this->employee = AuthHolder::getInstance()->getAuth(AuthGurdTypeEnum::EMPLOYEE->value);
    }

    public function fetchAboutUs(AboutUsFilterDTO $aboutUsFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $aboutUs = $this->aboutUsRepository->getFirst();
            $resource = $aboutUs ? $this->HandelAboutDetailResource(
                $aboutUs,
                $view,
            ) : (object) [];
            return DataSuccess(
                status: true,
                message: 'about fetched successfully',
                data: $aboutUsFilterDto->paginate ? $resource->response()->getData() : $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function createOrUpdateAboutUs(AboutUsDTO $aboutUsDTO): DataStatus
    {
        try {
            DB::beginTransaction();
            // Step 1: Create or update about
            $aboutUs = $this->aboutUsRepository->updateOrCreate($aboutUsDTO);
            $aboutUs->refresh();
            // Step 2: Create or update about details
            $aboutUsDTO->about_us_id = $aboutUs->id;
            $this->createOrUpdateAboutUsDetails($aboutUsDTO);
            $this->createOrUpdateAboutUsImages($aboutUsDTO);
            DB::commit();
            return DataSuccess(
                status: true,
                message: isset($aboutUsDTO->about_us_id)
                ? 'About Us updated successfully'
                : 'About Us created successfully',
                data: new AboutUsDetailResource($aboutUs)
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    private function createOrUpdateAboutUsDetails(AboutUsDTO $aboutUsDTO)
    {
        if (isset($aboutUsDTO->statistics) && is_array($aboutUsDTO->statistics)) {
            //
            $this->aboutUsRepository->deleteAboutUsStatistics();
            foreach ($aboutUsDTO->statistics as $statisticData) {
                /**
                 * @var mixed
                 */
                $statisticDTO = AboutUsStatisticsDTO::fromArray($statisticData);
                $statisticDTO->about_us_id = $aboutUsDTO->about_us_id;

                if (isset($statisticData['id'])) {
                    $statisticDTO->about_us_statistics_id = $statisticData['id'];
                }
                $this->aboutUsStatisticRepository->create($statisticDTO);
            }
        }
    }

    private function createOrUpdateAboutUsImages(AboutUsDTO $aboutUsDTO)
    {
        if (isset($aboutUsDTO->images) && is_array($aboutUsDTO->images)) {
            foreach ($aboutUsDTO->images as $imageData) {
                /**
                 * @var mixed
                 */
                $imageDTO = AboutUsImageDTO::fromArray($imageData);
                $imageDTO->about_us_id = $aboutUsDTO->about_us_id;

                if (isset($imageData['id'])) {
                    $this->aboutUsImageRepository->update($imageData['id'], $imageDTO);
                } else {
                    $this->aboutUsImageRepository->create($imageDTO);
                }
            }
        }
    }


    public function deleteAboutUsImage(AboutUsImageDTO $aboutUsImageDTO): DataStatus
    {
        try {
            $this->aboutUsImageRepository->delete($aboutUsImageDTO->about_us_image_id);

            return DataSuccess(
                status: true,
                message: 'Image deleted successfully'
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function HandelAboutDetailResource($aboutUs, $viewType)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return AboutUsDetailResource::make($aboutUs);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return AboutUsDetailResource::make($aboutUs);
        } else {
            return AboutUsDetailResource::make($aboutUs);
        }
    }
}
