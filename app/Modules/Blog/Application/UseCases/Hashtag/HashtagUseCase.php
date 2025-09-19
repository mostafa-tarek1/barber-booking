<?php

namespace App\Modules\Blog\Application\UseCases\Hashtag;

use App\Modules\Base\Domain\Holders\AuthHolder;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Blog\Application\DTOS\Hashtag\HashtagDTO;
use App\Modules\Blog\Application\DTOS\Hashtag\HashtagFilterDTO;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Blog\Http\Resources\Api\Hashtag\WebSiteHashtagDetailResource;
use App\Modules\Blog\Http\Resources\Api\Hashtag\WebSiteHashtagResource;
use App\Modules\Blog\Http\Resources\Hashtag\HashtagDetailResource;
use App\Modules\Blog\Http\Resources\Hashtag\HashtagResource;
use App\Modules\Blog\Infrastructure\Persistence\Repositories\Hashtag\HashtagRepository;
use Illuminate\Support\Facades\DB;

class HashtagUseCase
{

    protected $hashtagRepository;
    protected $employee;

    public function __construct(HashtagRepository $hashtagRepository)
    {
        $this->hashtagRepository = $hashtagRepository;
        $this->employee = AuthHolder::getInstance()->getAuth(AuthGurdTypeEnum::EMPLOYEE->value);
    }

    public function fetchHashtags(HashtagFilterDTO $hashtagFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $hashtags = $this->hashtagRepository->filter(
                $hashtagFilterDto,
                operator: 'like',
                translatableFields: ['title', 'description'],
                paginate: $hashtagFilterDto->paginate,
                limit: $hashtagFilterDto->limit
            );
            $resource = $this->HandelHashtagResource(
                $hashtags,
                $view,
                $hashtagFilterDto->paginate
            );
            return DataSuccess(
                status: true,
                message: 'Hashtags fetched successfully',
                data: $hashtagFilterDto->paginate ? $resource->response()->getData() : $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function fetchHashtagDetails(HashtagFilterDTO $hashtagFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $hashtag = $this->hashtagRepository->getById($hashtagFilterDto->hashtag_id);
            $resource = $this->HandelHashtagDetailResource(
                $hashtag,
                $view,
                false
            );
            return DataSuccess(
                status: true,
                message: 'Hashtag fetched successfully',
                data: $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function createHashtag(HashtagDTO $hashtagDTO): DataStatus
    {
        try {
            DB::beginTransaction();
            $hashtagDTO->created_by = $this->employee->id;
            $hashtag = $this->hashtagRepository->create($hashtagDTO);
            $hashtag->refresh();
            // step 2: sync categories
            // $hashtag->categories()->sync($hashtagDTO->categories_ids); // sync categories with hashtag Directly
            // dd($hashtag->categories)
            $hashtagDTO->hashtag_id = $hashtag->id;
            DB::commit();
            return DataSuccess(
                status: true,
                message: 'hashtag created successfully',
                data: new HashtagResource($hashtag)
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    // private function updateHashtagCategories(UpdateCategoryHashtagsDTO $dto): DataStatus
    // {
    //     try {
    //         $hashtag = $this->hashtagRepository->getById($dto->hashtag_id);

    //         // Sync categories
    //         $hashtag->categories()->sync($dto->categories_ids);

    //         return DataSuccess(
    //             status: true,
    //             message: 'Hashtag categories updated successfully',
    //             data: new HashtagResource($hashtag->fresh())
    //         );
    //     } catch (\Exception $e) {
    //         return DataFailed(
    //             status: false,
    //             message: $e->getMessage()
    //         );
    //     }
    // }


    public function updateHashtag(HashtagDTO $hashtagDTO): DataStatus
    {
        try {
            $hashtagDTO->updated_by = $this->employee->id;
            $hashtag = $this->hashtagRepository->update($hashtagDTO->hashtag_id, $hashtagDTO);
            $hashtagDTO->hashtag_id = $hashtag->id;
            $hashtag->refresh();
            return DataSuccess(
                status: true,
                message: 'Hashtag updated successfully',
                data: new HashtagResource($hashtag)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function deleteHashtag(HashtagFilterDTO $hashtagFilterDto): DataStatus
    {
        try {
            $this->hashtagRepository->delete($hashtagFilterDto->hashtag_id);

            return DataSuccess(
                status: true,
                message: 'Hashtag deleted successfully',
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }




    private function HandelHashtagResource($hashtags, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return HashtagResource::collection($hashtags);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return WebSiteHashtagResource::collection($hashtags);
        } else {
            return HashtagResource::collection($hashtags);
        }
    }

    public function HandelHashtagDetailResource($hashtag, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return HashtagDetailResource::make($hashtag);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return WebSiteHashtagDetailResource::make($hashtag);
        } else {
            return HashtagDetailResource::collection($hashtag);
        }
    }
}
