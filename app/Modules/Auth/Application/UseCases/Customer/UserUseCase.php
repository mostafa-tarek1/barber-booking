<?php

namespace App\Modules\Auth\Application\UseCases\Customer;

use App\Modules\Auth\Application\DTOS\Customer\CheckCodeDTO;
use App\Modules\Auth\Application\DTOS\Customer\CheckCredentialDTO;
use App\Modules\Auth\Application\DTOS\Customer\LoginDTO;
use App\Modules\Auth\Application\DTOS\Customer\RegisterDTO;
use App\Modules\Auth\Application\DTOS\Customer\ResetPasswordDTO;
use App\Modules\Auth\Application\DTOS\Customer\UserAddressDTO;
use App\Modules\Auth\Application\DTOS\Customer\UserDTO;
use App\Modules\Auth\Application\DTOS\Customer\UserFilterDTO;
use App\Modules\Auth\Http\Resources\Customer\UserResource;
use App\Modules\Auth\Http\Resources\Dashboard\Customer\UserResource as DashboardUserResource;
use App\Modules\Auth\Http\Resources\Dashboard\Customer\UserDetailsResource as DashboardUserDetailsResource;
use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\User;
use App\Modules\Auth\Infrastructure\Persistence\Repositories\Customer\UserAddressRepository;
use App\Modules\Auth\Infrastructure\Persistence\Repositories\Customer\UserDevice\UserDeviceRepository;
use App\Modules\Auth\Infrastructure\Persistence\Repositories\Customer\UserRepository;
use App\Modules\Base\Application\Response\DataFailed;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Base\Application\Response\DataSuccess;
use App\Modules\Category\Application\Enums\View\ViewTypeEnum;
use App\Modules\Notification\Application\DTOS\Topic\TopicDTO;
use App\Modules\Notification\Application\DTOS\TopicUser\TopicUserDTO;
use App\Modules\Notification\Infrastructure\Persistence\Repositories\Topic\TopicRepository;
use App\Modules\Notification\Infrastructure\Persistence\Repositories\TopicUser\TopicUserRepository;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserUseCase
{

    protected $userRepository;
    protected $userDeviceRepository;
    protected $userAddressRepository;
    protected $topicRepository;
    protected $TopicUserRepository;
    /**
     *  @var User $user
     */
    protected $user;


    public function __construct(UserRepository $userRepository, UserAddressRepository $userAddressRepository)
    {
        $this->userRepository = $userRepository;
        $this->userAddressRepository = $userAddressRepository;
        $this->userDeviceRepository = new UserDeviceRepository();
        $this->topicRepository = new TopicRepository();
        $this->TopicUserRepository = new TopicUserRepository();
    }

    /**
     * Register a new user.
     *
     * @param RegisterDTO $registerDTO
     * @return DataStatus
     */
    public function register(RegisterDTO $registerDTO): DataStatus
    {
        try {
            // Step 1 : create new user in the database
            DB::beginTransaction();
            /** @var User $user */
            $user = $this->userRepository->create($registerDTO);
            if (!$user || !$user->id || $user instanceof Exception) {
                DB::rollBack();
                return new DataFailed(
                    statusCode: Response::HTTP_BAD_REQUEST,
                    message: ' failed to create user'
                );
            }
            $user = $user->fresh();
            if ($user) {
                // Step 2 : create user device with
                if ($registerDTO->UserDevice() != []) {
                    $user->userDevices()->create($registerDTO->UserDevice());
                    // dd($registerDTO->UserDevice());
                    // $this->userDeviceRepository->create($registerDTO->UserDevice());
                }
                $user['api_token'] = $user->createToken('api_token')->plainTextToken;
                $credentials = $registerDTO->credential();

                $fieldType = checkCredentialType($credentials);

                // Step 3 : send OTP
                $user->sendOtp(fieldType: $fieldType, userRepository: $this->userRepository);

                // Step 4 : subscribe to topic if provided
                try {
                    $this->handleNotficationTopicSubscription($user);
                } catch (\Throwable $th) {
                    info(['error in subscribing to topic' => $th->getMessage()]);
                }
                // $location = $registerDTO->location_id != null ? $this->userRepository->getLocation($user->id) : null;
                // $nationality = $registerDTO->nationality_id != null ? $this->userRepository->getNationality($user->id) : null;
                // $user['location'] = $location != null ? $location['data'] : null;
                // $user['nationality'] = $nationality != null ? $nationality['data'] : null;
                DB::commit();
                return new DataSuccess(
                    message: 'success',
                    data: $user,
                    resourceData: new UserResource($user)
                );
            }
            DB::rollBack();
            return new DataFailed(
                statusCode: Response::HTTP_BAD_REQUEST,
                message: ' failed to create user'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            $statusCode = $this->handleStatusCode($e);
            return new DataFailed(
                statusCode: $statusCode,
                message: $e->getMessage()
            );
        }
    }

    public function handleNotficationTopicSubscription(User $user, string $baseName = 'register'): DataStatus
    {
        try {
            $baseName = 'register';
            // 1. Find an existing topic with available capacity
            $topic = $this->topicRepository->getAvailableTopicByBaseName($baseName);

            // 2. If no topic found, create a new one with incremented order
            if (!$topic) {
                $order = $this->topicRepository->getNextOrderForBaseName($baseName);
                $topicDTO = TopicDTO::fromArray([
                    'name' => $baseName . '-' . $order,
                    'userIdToSubscribe' => $user->id,
                    'order' => $order,
                ]);
                $topic = $this->topicRepository->create($topicDTO);
            }
            if (!$topic || !$topic->id || $topic instanceof Exception) {
                return new DataFailed(
                    statusCode: Response::HTTP_BAD_REQUEST,
                    message: ' failed to create topic'
                );
            }
            // 3. Subscribe user to topic
            $TopicUserDTO = TopicUserDTO::fromArray([
                'topic_id' => $topic->id,
                'user_id' => $user->id,
                'type' => $topic->type
            ]);
            $this->TopicUserRepository->create($TopicUserDTO);

            $subscribeTopicResponse = $this->topicRepository->subscripeToTopic(TopicDTO::fromArray([
                'userIdToSubscribe' => $user->id,
                'name' => $topic->name
            ]), $topic);
            // (Optional) You may want to refresh the topic count here if needed

            return $subscribeTopicResponse;

        } catch (\Throwable $th) {
            return new DataFailed(
                message: $th->getMessage()
            );
        }
    }

    /**
     * Log in a user.
     *
     * @param LoginDTO $loginDTO
     * @return DataStatus
     */
    public function login(LoginDTO $loginDTO): DataStatus
    {
        try {
            // Log::info(['loginDto' => $loginDTO->toArray()]);
            $user = $this->userRepository->login($loginDTO);
            if ($user) {
                // dd($user ,'login');
                if ($loginDTO->UserDevice() != []) {
                    $user->userDevices()->updateOrCreate(['device_id' => $loginDTO->device_id, 'device_type' => $loginDTO->device_type], $loginDTO->UserDevice());
                }

                $user['api_token'] = $user->createToken('api_token')->plainTextToken;
                $credentials = $loginDTO->credential();

                $fieldType = checkCredentialType($credentials);
                if ($user->is_email_verified || $user->is_phone_verified) {
                    $user->sendOtp($fieldType, $this->userRepository);
                }
                $location = $user->location_id != null ? $this->userRepository->getLocation($user->id) : null;
                $nationality = $user->nationality_id != null ? $this->userRepository->getNationality($user->id) : null;
                $user['location'] = $location != null ? $location['data'] : null;
                $user['nationality'] = $nationality != null ? $nationality['data'] : null;
                // dd(new UserResource($user));
                return new DataSuccess(
                    message: 'success',
                    data: new UserResource($user)
                );
            }
            return new DataFailed(
                message: 'The provided credentials are incorrect.',
                statusCode: Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            $statusCode = $this->handleStatusCode($e);
            return new DataFailed(
                statusCode: $statusCode,
                message: $e->getMessage()
            );
        }
    }

    /**
     * Check user credentials and send OTP if valid.
     *
     * @param CheckCredentialDTO $checkCredentialDTO
     * @return DataStatus
     */
    public function checkCredential(CheckCredentialDTO $checkCredentialDTO): DataStatus
    {
        try {
            $user = $this->userRepository->checkCredential($checkCredentialDTO);
            if ($user) {
                $credentials = $checkCredentialDTO->credential();
                $fieldType = checkCredentialType($credentials);
                try {

                    $user->sendOtp($fieldType, $this->userRepository);
                } catch (\Exception $e) {
                }
                return new DataSuccess(
                    status: true,
                    message: 'success',
                    data: true
                );
            }
            return new DataFailed(
                message: 'The provided credentials are incorrect.',
                statusCode: Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            $statusCode = $this->handleStatusCode($e);
            return new DataFailed(
                statusCode: $statusCode,
                status: false,
                message: $e->getMessage()
            );
        }
    }

    /**
     * Verify the provided OTP code.
     *
     * @param CheckCodeDTO $checkCodeDTO
     * @return DataStatus
     */
    public function checkCode(CheckCodeDTO $checkCodeDTO): DataStatus
    {
        try {
            DB::beginTransaction();
            $user = $this->userRepository->checkCredential($checkCodeDTO);

            if ($user) {
                $user = $this->userRepository->checkChangeRequest($user->id, $checkCodeDTO->credential());
                $this->userRepository->deleteChangeRequest($user->id);
            }
            $credentials = $checkCodeDTO->credential();

            $fieldType = checkCredentialType($credentials);
            if ($fieldType == 'email') {
                $user->is_email_verified = true;
                $user->email_verified_at = now();
                $user->save();
            } else {
                $user->is_phone_verified = true;
                $user->phone_verified_at = now();
                $user->save();
            }

            $cachedOtp = getCache("otp_{$user->$fieldType}");

            if (!$cachedOtp || !checkOTP($checkCodeDTO->code, $cachedOtp)) {
                DB::rollBack();
                return new DataFailed(
                    statusCode: Response::HTTP_BAD_REQUEST,
                    message: 'The provided code is incorrect.'
                );
            }

            forgetCache("otp_{$user->$fieldType}");
            DB::commit();
            return new DataSuccess(
                status: true,
                message: 'success',
                data: true
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return new DataFailed(
                statusCode: $this->handleStatusCode($e),
                message: $e->getMessage()
            );
        }
    }

    /**
     * Reset the user's password using the provided OTP code.
     *
     * @param ResetPasswordDTO $resetPasswordDTO
     * @return DataStatus
     */
    public function resetPassword(ResetPasswordDTO $resetPasswordDTO): DataStatus
    {
        try {
            $user = $this->userRepository->checkCredential($resetPasswordDTO);
            $credentials = $resetPasswordDTO->credential();
            $fieldType = checkCredentialType($credentials);
            $cachedOtp = getCache("otp_{$user->$fieldType}");
            if (!$cachedOtp || !checkOTP($resetPasswordDTO->code, $cachedOtp)) {
                return new DataFailed(
                    status: false,
                    message: 'The provided code is incorrect.'
                );
            }
            $this->userRepository->update($user->id, $resetPasswordDTO);
            // Remove OTP from cache after successful reset
            forgetCache("otp_{$user->$fieldType}");
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

    public function fetchUsers(UserFilterDTO $userFilterDTO, $viewType = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            // dd($userFilterDTO);
            $users = $this->userRepository->filter(
                dto: $userFilterDTO,
                paginate: $userFilterDTO->paginate,
                limit: $userFilterDTO->limit
            );

            $resource = $this->HandelUsersResource(
                $users,
                $viewType,
                $userFilterDTO->paginate
            );

            return new DataSuccess(
                status: true,
                message: 'success',
                data: $userFilterDTO->paginate
                ? $resource->response()->getData(true)
                : $resource
            );
        } catch (\Exception $e) {
            return new DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function fetchUserDetails(UserDTO $userDTO, $viewType = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $user = $this->userRepository->getById($userDTO->user_id);
            $resource = $this->HandelCategoryDetailResource($user, $viewType);
            return new DataSuccess(
                status: true,
                message: 'success',
                data: $resource
            );
        } catch (\Exception $e) {
            return new DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function createUser(UserDTO $UserDTO): DataStatus
    {
        try {
            DB::beginTransaction();
            $user = $this->userRepository->create($UserDTO);
            if (!$user || !$user->id || $user instanceof Exception) {
                DB::rollBack();
                return new DataFailed(
                    statusCode: Response::HTTP_BAD_REQUEST,
                    message: ' failed to create user'
                );
            }
            $user = $user->fresh();
            if ($user) { // Step 4 : subscribe to topic if provided
                try {
                    $this->handleNotficationTopicSubscription($user);
                } catch (\Throwable $th) {
                    info(['error in subscribing to topic' => $th->getMessage()]);
                }
            }
            // Step 2: update user location
            $UserDTO->user_id = $user->id;
            $this->handleUserAddress($UserDTO);
            DB::commit();
            return new DataSuccess(
                status: true,
                message: 'success',
                data: new DashboardUserResource($user)
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return new DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function updateUser(UserDTO $UserDTO): DataStatus
    {
        try {
            DB::beginTransaction();
            // dd($UserDTO->toArray());
            $user = $this->userRepository->update($UserDTO->user_id, $UserDTO);
            // Step 2: update user location
            $this->handleUserAddress($UserDTO);
            DB::commit();
            return new DataSuccess(
                status: true,
                message: 'success',
                data: new DashboardUserDetailsResource($user) //DashboardUserResource($user)
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return new DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function handleUserAddress(UserDTO $userDTO)
    {
        $user_id = $userDTO->user_id;
        // Step 1 : update user national address which is master
        $national_address = $userDTO->national_address;
        if (isset($national_address)) {
            $national_address = array_filter(array_merge($national_address, [
                'user_id' => $user_id,
                'is_master' => true,
                // 'file_address' => uploadImage(file:$national_address['file_address'],folder: 'users/'.$user_id . '/addresses'),
            ]));

            $nationalAddressDto = UserAddressDTO::fromArray($national_address);
            $this->userAddressRepository->updateOrCreate($nationalAddressDto);
        }

        // Step 2 : update user other addresses
        if (isset($userDTO->another_address)) {
            $another_addresses = $userDTO->another_address;
            // dd($another_addresses);
            // $sentAddressesIds1 = array_filter(array_column($another_addresses, 'id'),fn($id) => !is_null($id));
            $sentAddressesIds = array_filter(array_map(fn($a) => $a['id'] ?? null, $another_addresses));

            // 1. Delete addresses for this user_id that are NOT in the sent IDs
            $this->userAddressRepository->deleteByUserIdExceptIds($user_id, $sentAddressesIds);
            foreach ($another_addresses as $key => $value) {
                $anotherAddressDto = UserAddressDTO::fromArray(array_filter([
                    'id' => $value['id'] ?? null,
                    'user_id' => $user_id,
                    'is_master' => false,
                    'file_address' => isset($value['file_address']) ? uploadImage(file: $value['file_address'], folder: 'users/' . $user_id . '/addresses') : null,
                    'address' => $value['address'] ?? null,
                    'lat' => $value['lat'] ?? null,
                    'lng' => $value['lng'] ?? null
                ]));

                $address = $this->userAddressRepository->create($anotherAddressDto);
                // $address = $this->userAddressRepository->updateOrCreate($anotherAddressDto);
                // dd($address);
            }
        }
    }



    public function deleteUser(UserDTO $UserDTO): DataStatus
    {
        DB::beginTransaction();

        try {
            $user_e = $this->userRepository->getById($UserDTO->user_id);

            activity()
                ->causedBy(Auth::user())
                ->performedOn($user_e)
                ->event('deleted')
                ->withProperties(['email' => $user_e->email, 'phone' => $user_e->phone])
                ->log('User deleted: ' . $UserDTO->user_id);

            $user = $this->userRepository->delete($UserDTO->user_id);

            DB::commit();

            return new DataSuccess(
                status: true,
                message: 'success',
                data: true
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return new DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function forceLogout(UserDTO $UserDTO): DataStatus
    {
        try {
            $user = $this->userRepository->forceLogout($UserDTO->user_id);
            return new DataSuccess(
                status: true,
                message: 'success',
                data: true
            );
        } catch (\Exception $e) {
            return new DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function blockUser(UserDTO $UserDTO): DataStatus
    {
        try {
            $user_e = $this->userRepository->getById($UserDTO->user_id);
            activity()
                ->causedBy(Auth::user())
                ->performedOn($user_e)
                ->event('blocked')
                ->withProperties(['email' => $user_e->email, 'phone' => $user_e->phone])
                ->log('User blocked: ' . $UserDTO->user_id);

            $user = $this->userRepository->blockUser($UserDTO->user_id);
            return new DataSuccess(
                status: true,
                message: 'success',
                data: true
            );
        } catch (\Exception $e) {
            return new DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function unblockUser(UserDTO $UserDTO): DataStatus
    {
        try {
            $user_e = $this->userRepository->getById($UserDTO->user_id);
            activity()
                ->causedBy(Auth::user())
                ->performedOn($user_e)
                ->event('unblocked')
                ->withProperties(['email' => $user_e->email, 'phone' => $user_e->phone])
                ->log('User unblocked: ' . $UserDTO->user_id);
            $user = $this->userRepository->unblockUser($UserDTO->user_id);
            return new DataSuccess(
                status: true,
                message: 'success',
                data: true
            );
        } catch (\Exception $e) {
            return new DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }
    public function toggleUserApproval(UserDTO $userDTO): DataStatus
    {
        try {
            $user = $this->userRepository->getById($userDTO->user_id);
            $user->update(['is_approved' => !$user->is_approved]);
            $user->refresh();
            if ($user->is_approved) {
                $message = 'Approved';
            } else {
                $message = 'Unapproved';
            }
            activity()
                ->causedBy(Auth::user())
                ->performedOn($user)
                ->event('approved')
                ->withProperties(['email' => $user->email, 'phone' => $user->phone])
                ->log('User ' . $user->id . ' ' . $message);
            return new DataSuccess(
                status: true,
                message: $message,
                data: new DashboardUserResource($user)
            );
        } catch (\Exception $e) {
            return new DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }



    public function getUsersDeviceTokens(UserFilterDTO $userFilterDTO): DataStatus
    {
        try {
            $device_tokens = $this->userDeviceRepository->getWhereIn('user_id', $userFilterDTO->user_ids)->pluck('device_token');

            if ($device_tokens->isEmpty()) {
                return new DataFailed(
                    status: false,
                    message: 'No users found for the given user IDs.'
                );
            }
            return new DataSuccess(
                status: true,
                message: 'success',
                data: $device_tokens
            );
        } catch (\Exception $e) {
            return new DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function getAllUserIds(): DataStatus
    {
        try {
            $userIds = $this->userRepository->getAll()->pluck('id');
            return new DataSuccess(
                status: true,
                message: 'success',
                data: $userIds
            );
        } catch (\Exception $e) {
            return new DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    private function handleStatusCode(Exception $e)
    {
        $statusCode = $e->getCode() && $e->getCode() != 0 && $e->getCode() < 500
            ? $e->getCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR; // or another default
        return $statusCode;
    }

    private function HandelUsersResource($categories, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            $resource = DashboardUserResource::collection($categories);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            $resource = UserResource::collection($categories);
        } else {
            $resource = DashboardUserResource::collection($categories);
        }
        return $resource;
    }

    public function HandelCategoryDetailResource($category, $viewType)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return DashboardUserDetailsResource::make($category);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return UserResource::make($category);
        } else {
            return DashboardUserDetailsResource::make($category);
        }
    }
}
