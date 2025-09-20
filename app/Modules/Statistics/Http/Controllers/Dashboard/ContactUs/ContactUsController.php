<?php

namespace App\Modules\Statistics\Http\Controllers\Dashboard\ContactUs;

use App\Http\Controllers\Controller;
use App\Modules\Statistics\Application\UseCases\ContactUs\ContactUsUseCase;
use App\Modules\Statistics\Http\Requests\Dashboard\ContactUs\ContactUsIdRequest;
use App\Modules\Statistics\Http\Requests\Dashboard\ContactUs\CreateContactUsRequest;
use App\Modules\Statistics\Http\Requests\Dashboard\ContactUs\FetchContactUsRequest;
class ContactUsController extends Controller
{
    protected $contactUsUseCase;

    public function __construct(ContactUsUseCase $contactUsUseCase)
    {
        $this->contactUsUseCase = $contactUsUseCase;
    }

    public function fetchContactUs(FetchContactUsRequest $request)
    {
        return $this->contactUsUseCase->fetchContactUs($request->toDTO())->response();
    }

    public function fetchContactUsDetails(ContactUsIdRequest $request)
    {
        return $this->contactUsUseCase->fetchContactUsDetails($request->toDTO())->response();
    }

    public function createContactUs(CreateContactUsRequest $request)
    {
        return $this->contactUsUseCase->createContactUs($request->toDTO())->response();
    }

    public function deleteContactUs(ContactUsIdRequest $request)
    {
        return $this->contactUsUseCase->deleteContactUs($request->toDTO())->response();
    }
}
