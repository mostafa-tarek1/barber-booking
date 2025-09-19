<?php

namespace App\Modules\Statistics\Http\Controllers\Dashboard\Term;

use App\Http\Controllers\Controller;
use App\Modules\Statistics\Application\UseCases\Term\TermUseCase;
use App\Modules\Statistics\Http\Requests\Dashboard\Term\CreateOrUpdateTermRequest;
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
        return $this->termUseCase->fetch(dto: $data)->response();
    }

    public function createTerm(CreateOrUpdateTermRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->toDTO();
        return $this->termUseCase->updateOrCreate(dto: $data)->response();

    }



}
