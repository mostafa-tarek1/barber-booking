<?php

namespace App\Modules\Auth\Http\Controllers\Api\Customer;

use App\Modules\Auth\Application\UseCases\Customer\AuthUserUseCase;
use App\Modules\Auth\Http\Requests\Api\Customer\ChangePasswordRequest;
use App\Modules\Auth\Http\Requests\Api\Customer\LogoutRequest;
use App\Modules\Auth\Http\Requests\Api\Customer\UpdateAccountRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\Api\Customer\ChangePhoneRequest;
use App\Modules\Auth\Http\Requests\Api\Customer\DeleteAnotherAddressRequest;

class AuthUserController extends Controller
{
    protected $AuthUserUseCase;

    public function __construct(AuthUserUseCase $AuthUserUseCase)
    {
        $this->AuthUserUseCase = $AuthUserUseCase;
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Log out the authenticated user",
     *     tags={"Authenticated User"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="device_token", type="string", nullable=true, description="Device token for push notifications", example="token123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Authentication required"
     *     )
     * )
     */
    public function logout(LogoutRequest $request)
    {
        return $this->AuthUserUseCase->logout($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/api/change_password",
     *     summary="Change the authenticated user's password",
     *     tags={"Authenticated User"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="old_password", type="string", description="Current password", example="secret123"),
     *             @OA\Property(property="new_password", type="string", description="New password", example="newsecret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid old password or bad request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Authentication required"
     *     )
     * )
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        return $this->AuthUserUseCase->changePassword($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/api/update_account",
     *     summary="Update the authenticated user's account details",
     *     tags={"Authenticated User"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", nullable=true, description="User's full name", example="John Doe")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account updated successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Authentication required"
     *     )
     * )
     */
    public function updateAccount(UpdateAccountRequest $request)
    {
        return $this->AuthUserUseCase->updateAccount($request->toDTO())->response();
    }
    
    /**
     * @OA\Post(
     *     path="/api/change_phone",
     *     summary="create the authenticated user's phone number request",
     *     tags={"Authenticated User"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="phone", type="string", nullable=false, description="User's new phone", example="6465463")
     *             @OA\Property(property="phone_code", type="string", nullable=false, description="User's new phone code", example="+20")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Phone updated successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Authentication required"
     *     )
     * )
     */
    public function changePhone(ChangePhoneRequest $request)
    {
        return $this->AuthUserUseCase->changePhone($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/api/delete_account",
     *     summary="Delete the authenticated user's account",
     *     tags={"Authenticated User"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=204,
     *         description="Account deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Authentication required"
     *     )
     * )
     */
    public function deleteAccount()
    {
        return $this->AuthUserUseCase->deleteAccount()->response();
    }
    
    /**
     * @OA\Post(
     *     path="/api/delete_another_address",
     *     summary="Delete the authenticated user's account",
     *     tags={"Authenticated User"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=204,
     *         description="Account deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Authentication required"
     *     )
     * )
     */
    public function deleteAnotherAddress(DeleteAnotherAddressRequest $request)
    {
        $dto = $request->toDTO();
        return $this->AuthUserUseCase->deleteAnotherAddress($dto)->response();
    }
    
    /**
     * @OA\Post(
     *     path="/api/check_approved",
     *     summary="Check the approval status of the authenticated user's account",
     *     tags={"Authenticated User"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=204,
     *         description="Account Returned Successfuly"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Authentication required"
     *     )
     * )
     */
    public function checkApproved()
    {
        return $this->AuthUserUseCase->checkExist()->response();
    }

}
