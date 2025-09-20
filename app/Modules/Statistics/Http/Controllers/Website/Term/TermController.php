<?php

namespace App\Modules\Statistics\Http\Controllers\Website\Term;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Statistics\Application\UseCases\Term\TermUseCase;
use App\Modules\Statistics\Http\Requests\Dashboard\Term\FetchTermRequest;


class TermController extends Controller
{
    protected TermUseCase $termUseCase;

    public function __construct(TermUseCase $termUseCase)
    {
        $this->termUseCase = $termUseCase;
    }
    public function fetchTerms(FetchTermRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->toDTO();
        return $this->termUseCase->fetch(dto: $data , type: ViewTypeEnum::MOBILE->value)->response();
    }
}
