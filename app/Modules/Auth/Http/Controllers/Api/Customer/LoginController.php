<?php

namespace App\Modules\Auth\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\Api\Customer\LoginRequest;
use App\Modules\Auth\Application\DTOs\Customer\LoginDTO;
use App\Modules\Auth\Application\UseCases\Customer\LoginUseCase;

// If the class is in a different namespace or folder, update the import accordingly, for example:
// use App\Modules\Auth\Domain\UseCases\Customer\LoginUseCase;
// use Illuminate\Http\JsonResponse;

// class LoginController extends Controller
// {
//     /**
//      * @var LoginUseCase
//      */
//     protected $loginUseCase;

    // public function __construct(LoginUseCase $loginUseCase)
    // {
    //     $this->loginUseCase = $loginUseCase;
    // }

//     public function __invoke(LoginRequest $request)
//     {
//             $dto = LoginDTO::fromArray($request->validated());
//             $result = $this->loginUseCase->login($dto);
//             if (is_array($result)) {
//                 return response()->json($result);
//             }
//             return $result->response();
//     }
// }

