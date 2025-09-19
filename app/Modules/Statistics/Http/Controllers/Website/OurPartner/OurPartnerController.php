<?php

namespace App\Modules\Statistics\Http\Controllers\Website\OurPartner;

use App\Modules\Statistics\Application\UseCases\OurPartner\OurPartnerUseCase;
use App\Modules\Statistics\Http\Requests\Dashboard\OurPartner\FetchOurPartnerRequest;
use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;




class OurPartnerController extends Controller
{
    protected $ourPartnerUseCase;

    public function __construct(OurPartnerUseCase $ourPartnerUseCase)
    {
        $this->ourPartnerUseCase = $ourPartnerUseCase;
    }

    public function fetchOurPartners(FetchOurPartnerRequest $request)
    {
        return $this->ourPartnerUseCase->fetchOurPartners($request->toDTO() , ViewTypeEnum::MOBILE->value)->response();
    }
}
