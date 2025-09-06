<?php

namespace App\Modules\Auth\Application\DTOS\Customer;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;
use App\Modules\User\Domain\DTO\UserDTOInterface;

class UserDTO extends BaseDTOAbstract
{
    public  $user_id;
    public  $name;
    public $is_approved;
    public $is_blocked;
    public $is_verified;
    public $is_phone_verified;
    public $is_email_verified;
    public  $first_name;
    public  $last_name;
    public  $username;
    public  $email;
    public  $phone;
    public  $password;
    public  $identifyNumber;
    public $image;
    public $tax_register;
    public $commercial_register;
    public $national_address;
    public $another_address;
    public string $imageFolder = "users";
    
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function excludedAttributes(): array
    {
        return [
            "national_address",
            "another_address"
        ];
    }

    public function handleImageFolder(): string
    {
        return $this->imageFolder ;//. DIRECTORY_SEPARATOR . $this->user_id;
    }
}
