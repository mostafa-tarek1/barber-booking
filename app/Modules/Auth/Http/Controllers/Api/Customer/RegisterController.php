<?php

namespace App\Modules\Auth\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Application\DTOS\Customer\CustomerDTO;
use App\Modules\Auth\Application\UseCases\Customer\RegisterUseCase;
use App\Modules\Auth\Http\Requests\Api\Customer\RegisterRequest;
use App\Modules\Auth\Application\DTOS\Customer\RegisterDTO;

class RegisterController extends Controller
{
    public function __construct(
        protected RegisterUseCase $registerUseCase
    ) {}

    public function register(RegisterRequest $request)
    {
        $result = $this->registerUseCase->register($request->toDTO());

        return $result->response();
    }
}
