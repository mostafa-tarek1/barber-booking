<?php

namespace App\Modules\Auth\Infrastructure\Persistence\Repositories\Customer;

use App\Exceptions\BlockedUserException;
use App\Modules\Auth\Application\DTOS\Customer\UpdateAccountDTO;
use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\PhoneRequest\PhoneRequest;
use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\User;
use App\Modules\Base\Domain\ApiService\WhatsAppApiService;
use App\Modules\Base\Domain\DTO\BaseDTOInterface;
use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Notification\Application\Services\NotificationService;
use App\Modules\Notification\Domain\Enums\NotificationTypeEnum;
use App\Modules\Order\Application\Enums\Order\OrderStatusTypeEnum;
use App\Modules\User\Infrastructure\Persistence\ApiService\LocationApiService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserRepository extends BaseRepositoryAbstract
{
    /**
     * @var User
     */
    protected $user;

    protected $whatsAppApiService;

    protected $stageApiService;

    protected $locationApiService;

    protected $notificationApiService;

    public function __construct()
    {
        $this->setModel(new User);
        $this->user = Auth::user();
        $this->whatsAppApiService = new WhatsAppApiService;
        $this->notificationApiService = new NotificationService;

        // $this->locationApiService = new LocationApiService();
        // $this->notificationApiService = new NotificationApiService();
    }

    public function login($loginDTO): User
    {
        try {
            // dd($loginDTO);
            $credentials = $loginDTO->toArray();
            $fieldType = array_key_first($credentials);
            // dd($credentials, $fieldType);
            $user = $this->getWhere($fieldType, $credentials[$fieldType], 'first');
            if (! isset($user) && PhoneRequest::where($fieldType, $credentials[$fieldType])->exists()) {
                $otp = '123456'; // random_int(100000, 999999);
                setCache("otp_{$loginDTO->phone}", $otp, now()->addMinutes(5));
                $this->sendWhatsAppMessage(phone: $loginDTO->phone, countryCode: $loginDTO->phone_code, message: "Your OTP is: $otp");
                throw new \Exception('The provided credentials are incorrect.', Response::HTTP_BAD_REQUEST);
            } elseif (! isset($user) || ! Hash::check($loginDTO->password, $user->password)) {
                throw new \Exception('The provided credentials are incorrect.', Response::HTTP_BAD_REQUEST);
            } elseif ($user->is_blocked) {
                $user->tokens()->delete();
                throw new BlockedUserException;
            }

            return $user;
        } catch (BlockedUserException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function createPhoneRequest($userId, UpdateAccountDTO $updateAccountDTO): User
    {
        try {
            /** @var User $user */
            $user = $this->getWhere('id', $userId, 'first');
            if (! $user) {
                throw new \Exception('User not found.');
            }
            $user->phoneRequests()->create($updateAccountDTO->toArray());

            return $user;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function checkCredential($checkCredentialDTO): User|bool
    {
        try {
            $credentials = $checkCredentialDTO->credential();
            $fieldType = array_key_first($credentials);
            // dd($fieldType, $credentials[$fieldType]);
            $is_exist_user = $this->checkExist($fieldType, $credentials[$fieldType], 'first');
            if ($is_exist_user) {
                $user = $this->getWhere($fieldType, $credentials[$fieldType], 'first');
            } elseif (isset($checkCredentialDTO->user_id)) {
                $phoneRequest = PhoneRequest::where('user_id', $checkCredentialDTO->user_id)->where('phone', $credentials['phone'])->first();
                if ($phoneRequest) {
                    $user = $this->getWhere('id', $phoneRequest->user_id, 'first');
                } else {
                    throw new \Exception('The provided credentials are incorrect.', Response::HTTP_BAD_REQUEST);
                }
            } else {
                throw new \Exception('The provided credentials are incorrect.', Response::HTTP_BAD_REQUEST);
            }

            return $user;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function checkChangeRequest($userId, $credentials)
    {
        try {
            $user = $this->getWhere('id', $userId, 'first');
            if (! $user) {
                throw new \Exception('User not found.', Response::HTTP_BAD_REQUEST);
            }
            $user->update($credentials);

            return $user;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function deleteChangeRequest($userId)
    {
        try {
            $user = $this->getWhere('id', $userId, 'first');
            if (! $user) {
                throw new \Exception('User not found.', Response::HTTP_BAD_REQUEST);
            }
            $user->phoneRequests()->delete();

            return $user;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function logout($logoutDTO): bool
    {
        try {
            $user_device = $this->user->userDevices()->where('device_token', $logoutDTO->deviceToken)->first();
            if ($user_device) {
                $user_device->delete();
            }
            $this->user->tokens()->delete();

            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function sendWhatsAppMessage($phone, $countryCode, $message)
    {
        try {
            $handeledPhone = handelPhone($phone);
            $handledCountryCode = $handeledPhone['countryCode'] && ! empty($handeledPhone['countryCode']) ? $handeledPhone['countryCode'] : handelPhone($countryCode)['countryCode'];
            $response = $this->whatsAppApiService->sendMessage($handeledPhone['phone'], $message, config('services.whatsapp.session'), $handledCountryCode);

            return $response;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getLocation($userId)
    {
        try {
            $user = $this->getWhere('id', $userId, 'first');
            if (! $user) {
                throw new \Exception('User not found.');
            }
            $location = $this->locationApiService->fetchLocationDetails($user->location_id);
            if (! $location) {
                throw new \Exception('Location not found.');
            }

            return $location;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getNationality($userId)
    {
        try {
            $user = $this->getWhere('id', $userId, 'first');
            if (! $user) {
                throw new \Exception('User not found.');
            }
            $location = $this->locationApiService->fetchLocationDetails($user->nationality_id);
            if (! $location) {
                throw new \Exception('Location not found.');
            }

            return $location;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function forceLogout($userId)
    {
        try {
            $user = $this->getWhere('id', $userId, 'first');
            if (! $user) {
                throw new \Exception('User not found.');
            }
            $user->tokens()->delete();

            // $response = $this->notificationApiService->sendNotification(notificationDTO: $notificationDTO, topic: $topic, tokens: $tokens);
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function blockUser($userId)
    {
        try {
            $user = $this->getWhere('id', $userId, 'first');
            if (! $user) {
                throw new \Exception('User not found.');
            }
            $user->update(['is_blocked' => true]);
            $this->notificationApiService->sendToUser(
                $user->id,
                NotificationTypeEnum::SYSTEM_ALERT,
                'User Blocked',
                'Your account has been blocked by the admin.'
            );

            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function unblockUser($userId)
    {
        try {
            $user = $this->getWhere('id', $userId, 'first');
            if (! $user) {
                throw new \Exception('User not found.');
            }
            $user->update(['is_blocked' => false]);

            $this->notificationApiService->sendToUser(
                $user->id,
                NotificationTypeEnum::SYSTEM_ALERT,
                'User Unblocked',
                'Your account has been unblocked by the admin.'
            );

            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Filter records dynamically, supporting translations and array values
     */
    public function filter(
        BaseDTOInterface $dto,
        string $operator = 'like',
        array $translatableFields = [],
        $paginate = false,
        $limit = 10,
        array $whereHasRelations = [],
        array $whereHasMultipleRelations = [],
        ?string $pluckField = null
    ): Collection|LengthAwarePaginator|SupportCollection {
        try {
            $query = $this->getModel()->query();
            $query->when(isset($dto->word), function ($q) use ($dto) {
                $q->where(function ($q2) use ($dto) {
                    $q2->where('name', 'like', "%{$dto->word}%")
                        ->orWhere('email', 'like', "%{$dto->word}%")
                        ->orWhere('phone', 'like', "%{$dto->word}%");
                });
            });
            // dd($dto->toArray());
            foreach ($dto->toArray() as $key => $value) {
                // Handle computed fields
                if ($key === 'has_commercial_register') {
                    if ($value === true) {
                        $query->whereNotNull('commercial_register');
                    } elseif ($value === false) {
                        $query->whereNull('commercial_register');
                    }

                    continue;
                }

                if ($key === 'has_tax_register') {
                    if ($value === true) {
                        $query->whereNotNull('tax_register');
                    } elseif ($value === false) {
                        $query->whereNull('tax_register');
                    }

                    continue;
                }

                if ($key === 'has_orders') {
                    if ($value === true) {
                        $query->whereHas('orders');
                    } elseif ($value === false) {
                        $query->doesntHave('orders');
                    }

                    continue;
                }

                if ($key === 'has_current_orders') {
                    // dd($value);
                    if ($value === true) {
                        $query->whereHas('orders', function ($q) {
                            $q->whereIn('status', [OrderStatusTypeEnum::PEREPERATION->value, OrderStatusTypeEnum::AWAITING_APPROVAL->value, OrderStatusTypeEnum::AWAITING_RECEIPT->value]);
                        });
                    } elseif ($value === false) {
                        $query->doesntHave('orders', 'and', function ($q) {
                            $q->whereIn('status', [OrderStatusTypeEnum::PEREPERATION->value, OrderStatusTypeEnum::AWAITING_APPROVAL->value, OrderStatusTypeEnum::AWAITING_RECEIPT->value]);
                        });
                    }

                    continue;
                }

                if ($key === 'from_date') {
                    $query->where('created_at', '>=', $value);

                    continue;
                }

                if ($key === 'to_date') {
                    $query->where('created_at', '<=', $value);

                    continue;
                }

                if (! in_array($key, ['lat', 'lng', 'distance']) && filled($value)) {
                    $query
                        ->when(in_array($key, $translatableFields), fn ($q) => $q->whereTranslationLike($key, "%{$value}%"))
                        ->when(is_array($value), fn ($q) => $q->whereIn($key, $value))
                        ->when(is_bool($value), fn ($q) => $q->where($key, $value))
                        ->when(is_numeric($value), fn ($q) => $q->where($key, $operator, $value))
                        ->when(is_string($value) && ! in_array($key, $translatableFields), fn ($q) => $q->where($key, $operator, ($operator === 'like' ? "%{$value}%" : $value)));
                }
            }
            // Apply whereHas relations with whereIn
            foreach ($whereHasRelations as $relation => $conditions) {
                // dd($relation, $conditions);
                $query->whereHas($relation, function ($q) use ($conditions) {
                    if (is_callable($conditions)) {
                        // If it's a closure, execute it
                        $conditions($q);
                    } elseif (is_array($conditions)) {
                        foreach ($conditions as $key => $values) {
                            // dd($key, $values);
                            if (is_array($values)) {
                                // dd($key,$values);
                                $q->whereIn($key, $values); // Use whereIn for arrays
                                // dd($q->get());
                            } else {
                                $q->where($key, $values); // Use where for single values
                            }
                        }
                    }
                });
            }

            // dd($query->get());

            if (! empty($pluckField)) {
                // $query->select($pluckField);
                return $query->pluck($pluckField);
            }

            return $paginate ? $query->orderBy($dto->orderBy, $dto->direction)->paginate($limit) : $query->orderBy($dto->orderBy, $dto->direction)->get();
        } catch (Exception $e) {
            report($e);

            return collect();
        }
    }
}
