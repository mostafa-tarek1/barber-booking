<?php

namespace App\Modules\Auth\Http\Resources\Dashboard\Customer;

use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\User;
use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\UserAddress\UserAddress;
use App\Modules\Order\Http\Resources\Dashboard\Order\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailsResource extends UserResource
{
    /**
     * Transform the resource into an array.
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;
        $locations = $user->addresses()->orderBy('id', 'desc')->get();
        $data = array_merge(parent::toArray($request), [
            'location' => UserAddressResource::collection($locations),
            'commercial_register' => $user->commercial_register_link ?? "",
            'tax_register' => $user->tax_register_link ?? "",
            "orders" => OrderResource::collection($user->orders),
        ]);
        return $data;
    }
}
