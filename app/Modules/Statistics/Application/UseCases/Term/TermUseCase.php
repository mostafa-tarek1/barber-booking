<?php

namespace App\Modules\Statistics\Application\UseCases\Term;

use App\Modules\Base\Application\Response\DataSuccess;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Statistics\Http\Resources\Dashboard\Term\TermDetailResource;
use App\Modules\Statistics\Http\Resources\Dashboard\Term\TermResource;
use App\Modules\Statistics\Infrastructure\Persistence\Repositories\Term\TermRepository;

class TermUseCase
{
    protected TermRepository $termRepository;

    public function __construct(TermRepository $termRepository)
    {
        $this->termRepository = $termRepository;
    }

    public function fetch($dto, $type = ViewTypeEnum::DASHBOARD->value): DataSuccess
    {
        $term = $this->termRepository->getFirst();

        if (! $term) {
            return new DataSuccess(
                data: null,
                message: 'term fetched successfully'
            );
        }

        if ($type == ViewTypeEnum::DASHBOARD->value) {
            return new DataSuccess(
                data: TermDetailResource::make($term),
                message: 'term fetched successfully'
            );
        } else {
            return new DataSuccess(
                data: getTranslation('description', request()->header('Accept-Language'), $term),
                message: 'term fetched successfully'
            );
        }

    }

    public function updateOrCreate($dto): DataSuccess
    {
        $data = $this->termRepository->updateOrCreate($dto);

        return new DataSuccess(
            data: new TermResource($data),
            message: 'Term updated successfully'

        );
    }
}
