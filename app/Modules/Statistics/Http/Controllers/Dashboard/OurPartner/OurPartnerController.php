<?php

namespace App\Modules\Statistics\Http\Controllers\Dashboard\OurPartner;

use App\Http\Controllers\Controller;
use App\Modules\Statistics\Application\UseCases\OurPartner\OurPartnerUseCase;
use App\Modules\Statistics\Http\Requests\Dashboard\OurPartner\OurPartnerIdRequest;
use App\Modules\Statistics\Http\Requests\Dashboard\OurPartner\CreateOurPartnerRequest;
use App\Modules\Statistics\Http\Requests\Dashboard\OurPartner\FetchOurPartnerRequest;
use App\Modules\Statistics\Http\Requests\Dashboard\OurPartner\UpdateOurPartnerRequest;

class OurPartnerController extends Controller
{
    protected $ourPartnerUseCase;

    public function __construct(OurPartnerUseCase $ourPartnerUseCase)
    {
        $this->ourPartnerUseCase = $ourPartnerUseCase;
    }

    public function fetchOurPartners(FetchOurPartnerRequest $request)
    {
        return $this->ourPartnerUseCase->fetchOurPartners($request->toDTO())->response();
    }

    public function fetchOurPartnerDetails(OurPartnerIdRequest $request)
    {
        return $this->ourPartnerUseCase->fetchOurPartnerDetails($request->toDTO())->response();
    }

    public function createOurPartner(CreateOurPartnerRequest $request)
    {
        return $this->ourPartnerUseCase->createOurPartner($request->toDTO())->response();
    }

    public function updateOurPartner(UpdateOurPartnerRequest $request)
    {
        return $this->ourPartnerUseCase->updateOurPartner($request->toDTO())->response();
    }


    public function deleteOurPartner(OurPartnerIdRequest $request)
    {
        return $this->ourPartnerUseCase->deleteOurPartner($request->toDTO())->response();
    }
}
