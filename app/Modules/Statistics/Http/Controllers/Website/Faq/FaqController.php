<?php

namespace App\Modules\Statistics\Http\Controllers\Website\Faq;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Statistics\Application\UseCases\Faq\FaqUseCase;
use App\Modules\Statistics\Http\Requests\Dashboard\Faq\FetchFaqRequest;

class FaqController extends Controller
{
    protected $faqUseCase;

    public function __construct(FaqUseCase $faqUseCase)
    {
        $this->faqUseCase = $faqUseCase;
    }

    public function fetchFaqs(FetchFaqRequest $request)
    {
        return $this->faqUseCase->fetchFaqs($request->toDTO() , ViewTypeEnum::MOBILE->value)->response();
    }


}
