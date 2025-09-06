<?php

namespace App\Modules\Auth\Http\Controllers\Api\Customer;

use App\Modules\Auth\Application\UseCases\Customer\NationalAddressUseCase;
use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\Api\Customer\CreateOrUpdateUserAddressRequest;
use App\Modules\Auth\Http\Requests\Api\Customer\CreateOrUpdateUserAnotherAddressRequest;



class UserAddressController extends Controller
{
    protected $userAddressUseCase;

    public function __construct(NationalAddressUseCase $userAddressUseCase)
    {
        $this->userAddressUseCase = $userAddressUseCase;
    }

    public function createOrUpdateNationalAddress(CreateOrUpdateUserAddressRequest $request)
    {
        return $this->userAddressUseCase->createOrUpdateAddress($request->toDTO())->response();
    }

    public function updateOrCreateAnotherAddress(CreateOrUpdateUserAnotherAddressRequest $request)
    {
        return $this->userAddressUseCase->createOrUpdateAnotherAddress($request->toDTO())->response();
    }

}
