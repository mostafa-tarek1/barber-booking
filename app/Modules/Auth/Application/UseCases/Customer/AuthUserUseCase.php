<?php

namespace App\Modules\Auth\Application\UseCases\Customer;

use App\Modules\Auth\Application\DTOS\Customer\ChangePasswordDTO;
use App\Modules\Auth\Application\DTOS\Customer\LogoutDTO;
use App\Modules\Auth\Application\DTOS\Customer\UpdateAccountDTO;
use App\Modules\Auth\Application\DTOS\Customer\UserAddressDTO;
use App\Modules\Auth\Http\Resources\Customer\UserResource;
use App\Modules\Auth\Infrastructure\Persistence\Repositories\Customer\UserAddressRepository;
use App\Modules\Auth\Infrastructure\Persistence\Repositories\Customer\UserRepository;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Base\Domain\Holders\AuthHolder;
use Illuminate\Support\Facades\Hash;
use App\Modules\Auth\Infrastructure\Persistence\Repositories\Customer\UserDevice\UserDeviceRepository;
use App\Modules\Base\Application\Response\DataFailed;
use App\Modules\Base\Application\Response\DataSuccess;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthUserUseCase
{

    protected $userRepository;
    protected $userAddressRepository;
    /**
     *  @var User
     */

    protected $user;

    public function __construct(UserRepository $userRepository, UserAddressRepository $userAddressRepository)
    {
        $this->userRepository = $userRepository;
        $this->userAddressRepository = $userAddressRepository;
        $this->user = AuthHolder::getInstance()->getAuth(AuthGurdTypeEnum::USER->value);
        // Log::info(['user' => $this->user]);
    }
    public function logout(LogoutDTO $logoutDTO): DataStatus
    {
        try {
            $response = $this->userRepository->logout($logoutDTO);
            return new DataSuccess(
                status: true,
                message: 'success',
                data: $response
            );
        } catch (\Exception $e) {
            return new DataFailed(
                message: $e->getMessage()
            );
        }
    }


    public function changePassword(ChangePasswordDTO $changePasswordDTO): DataStatus
    {
        try {
            // step 0: check user is_blocked and delete token
            if ($this->user->is_blocked) {
                return new DataFailed(
                    statusCode: Response::HTTP_FORBIDDEN,
                    message: 'The account is blocked'
                );
            }
            // step 1: check old_password (Hash::check($changePasswordDTO->old_password, $this->user->password
            // dd($changePasswordDTO->old_password);
            // dd($this->user);
            if (!Hash::check($changePasswordDTO->old_password, $this->user->password)) {
                Log::info([$this->user]);
                Log::info(['user' => $this->user, 'old_password' => $changePasswordDTO->old_password, 'password' => $this->user->password]);
                return new DataFailed(
                    statusCode: Response::HTTP_NOT_ACCEPTABLE,
                    message: 'The old password is incorrect'
                );
            }
            if (Hash::check($changePasswordDTO->new_password, $this->user->password)) {
                return new DataFailed(
                    statusCode: Response::HTTP_CONFLICT,
                    message: 'The new password cannot be the same as the old password'

                );
            }
            $response = $this->userRepository->update($this->user->id, $changePasswordDTO);
            return new DataSuccess(
                message: 'success',
            );
        } catch (\Exception $e) {
            return new DataFailed(
                message: $e->getMessage()
            );
        }
    }


    public function updateAccount(UpdateAccountDTO $updateAccountDTO): DataStatus
    {
        try {
            DB::beginTransaction();
            // step 0: check user is_blocked
            if ($this->user->is_blocked) {
                return new DataFailed(
                    statusCode: Response::HTTP_FORBIDDEN,
                    message: 'Your account has been blocked by the admin.'
                );
            }
            // Step 1: update basic user data
            $user = $this->userRepository->update($this->user->id, $updateAccountDTO);
            if ($user) {
                $location = ($updateAccountDTO->location_id != null || $user->location_id != null) ? $this->userRepository->getLocation($this->user->id) : null;
                $nationality = ($updateAccountDTO->nationality_id != null || $user->nationality_id != null) ? $this->userRepository->getNationality($this->user->id) : null;
                $user['location'] = $location != null ? $location['data'] : null;
                $user['nationality'] = $nationality != null ? $nationality['data'] : null;
            }
            // Step 2: update user location
            $this->handleUserAddress($updateAccountDTO);
            DB::commit();
            return new DataSuccess(
                message: 'succehandleUserAddressss',
                data: new UserResource($user)
            );
        } catch (\Exception $e) {
            DB::rollBack();
            $updateAccountDTO::rollbackUploads();
            return new DataFailed(
                statusCode: $this->handleStatusCode($e),
                message: $e->getMessage()
            );
        }
    }

    public function changePhone(UpdateAccountDTO $updateAccountDTO): DataStatus
    {
        try {
            DB::beginTransaction();
            // Step 1: create phone request in the database
            $this->userRepository->createPhoneRequest($this->user->id, $updateAccountDTO);
            // Step 2: send otp to that phone
            $otp = '123456'; // random_int(100000, 999999);
            setCache("otp_{$updateAccountDTO->phone}", $otp, now()->addMinutes(5));
            $this->userRepository->sendWhatsAppMessage(phone: $updateAccountDTO->phone, countryCode: $updateAccountDTO->phone_code, message: "Your OTP is: $otp");

            DB::commit();
            return new DataSuccess(
                message: 'success',
            );
        } catch (\Exception $e) {
            DB::rollBack();
            $updateAccountDTO::rollbackUploads();
            return new DataFailed(
                statusCode: $this->handleStatusCode($e),
                message: $e->getMessage()
            );
        }
    }

    public function deleteAccount(): DataStatus
    {
        try {
            $this->userRepository->delete($this->user->id);
            return new DataSuccess(
                status: true,
                message: 'success',
            );
        } catch (\Exception $e) {
            return new DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function deleteAnotherAddress(UserAddressDTO $UserAddressDTO): DataStatus
    {
        try {
            $this->userAddressRepository->delete($UserAddressDTO->id);
            return new DataSuccess(
                status: true,
                message: 'success',
            );
        } catch (\Exception $e) {
            return new DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function checkExist(): DataStatus
    {
        try {
            $user = $this->userRepository->getWhere('id', $this->user->id, 'first');
            return new DataSuccess(
                status: true,
                message: 'success',
                data: new UserResource($user)
            );
        } catch (\Exception $e) {
            return new DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function handleUserAddress(UpdateAccountDTO $updateAccountDTO)
    {
        $user_id = $this->user->id;

        // Step 1: update user national address
        $national_address = $updateAccountDTO->national_address;
        if (isset($national_address)) {
            $national_address = array_filter(array_merge($national_address, [
                'user_id' => $user_id,
                'is_master' => true,
            ]));

            $userAddressDto = UserAddressDTO::fromArray($national_address);
            $this->userAddressRepository->updateOrCreate($userAddressDto);
        }

        // Step 2: update or create user another addresses
        $another_addresses = $updateAccountDTO->another_address;
        if (isset($another_addresses)) {
            foreach ($another_addresses as $key => $value) {
                $data = array_filter([
                    'user_id' => $user_id,
                    'is_master' => false,
                    'file_address' => isset($value['file_address'])
                        ? uploadImage(file: $value['file_address'], folder: 'users/' . $user_id . '/addresses')
                        : null,
                    'address' => $value['address'] ?? null,
                    'lat' => $value['lat'] ?? null,
                    'lng' => $value['lng'] ?? null,
                ]);

                if (!empty($value['id'])) {
                    // update existing
                    $data['id'] = $value['id'];
                    $userAddressDto = UserAddressDTO::fromArray($data);
                    $this->userAddressRepository->update($data['id'], $userAddressDto);
                    // dd($data);
                } else {
                    // dd($data);
                    // create new
                    $userAddressDto = UserAddressDTO::fromArray($data);
                    $this->userAddressRepository->create($userAddressDto);
                }
            }
        }
    }

    private function handleStatusCode(Exception $e)
    {
        $statusCode = $e->getCode() && $e->getCode() != 0 && $e->getCode() < 500
            ? $e->getCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR; // or another default
        return $statusCode;
    }
}
