<?php
namespace App\Modules\Auth\Infrastructure\Persistence\Repositories\Customer;

use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\Customer;
use Illuminate\Support\Facades\Hash;

class CustomerRepository
{
    public function create(array $data): Customer
    {
        return Customer::create([
            'name'     => $data['name'] ?? '',
            'email'    => $data['email'],
            'phone'    => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
