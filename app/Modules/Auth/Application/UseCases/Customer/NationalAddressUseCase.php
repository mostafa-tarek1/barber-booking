<?php

namespace App\Modules\Auth\Application\UseCases\Customer;
use App\Modules\Auth\Application\DTOS\Customer\UserAddressDTO;
use App\Modules\Auth\Application\DTOS\Customer\UserDTO;
use App\Modules\Auth\Http\Resources\Customer\UserAddressResource;
use App\Modules\Auth\Http\Resources\Customer\UserResource;
use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\User;
use App\Modules\Auth\Infrastructure\Persistence\Repositories\Customer\UserAddressRepository;
use App\Modules\Auth\Infrastructure\Persistence\Repositories\Customer\UserRepository;
use App\Modules\Base\Application\Response\DataFailed;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Base\Application\Response\DataSuccess;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Modules\Base\Domain\Holders\AuthHolder;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;



class NationalAddressUseCase
{

    protected $userRepository;
    protected $userAddressRepository;
    /**
     *  @var User $user
     */
    protected $user;


    public function __construct(UserRepository $userRepository, UserAddressRepository $userAddressRepository)
    {
        $this->userRepository = $userRepository;
        $this->userAddressRepository = $userAddressRepository;
        $this->user = AuthHolder::getInstance()->getAuth(AuthGurdTypeEnum::USER->value);
    }


    public function createOrUpdateAddress(UserAddressDTO $addressDTO): DataStatus
    {
        try {
            DB::beginTransaction();

            $addressDTO->is_master = true;

            $address = $this->userAddressRepository->updateOrCreate($addressDTO);
            $address->refresh();

            DB::commit();

            return DataSuccess(
                status: true,
                message: $address->wasRecentlyCreated
                ? 'Address created successfully'
                : 'Address updated successfully',
                data: new UserResource($address->user)
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return DataFailed(false, $e->getMessage());
        }
    }


    public function handleUserAddress(UserAddressDTO $userAddressDTO)
    {
        $user_id = $this->user->id;
        // Step 2: update or create user another addresses
        $another_addresses = $userAddressDTO->another_address;
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



    public function createOrUpdateAnotherAddress(UserAddressDTO $userAddressDTO): DataStatus
    {
        try {
            DB::beginTransaction();
            // $address = $this->userAddressRepository->updateOrCreate($userAddressDTO);
            // $address->refresh();
            $this->handleUserAddress($userAddressDTO);
            DB::commit();
            $user_id = $this->user->id;
            $user = $this->userRepository->getById($user_id);

            return DataSuccess(
                status: true,
                message: 'Another Address updated successfully',
                // message: $address->wasRecentlyCreated
                // ? 'Another Address created successfully'
                // : 'Another Address updated successfully',
                data: new UserResource($user)
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return DataFailed(false, $e->getMessage());
        }
    }
}
