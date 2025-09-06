<?php

namespace App\Modules\Auth\Http\Resources\Dashboard\Customer;

use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\UserAddress\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $token = ($request->bearerToken()) ? $request->bearerToken() : '';
        return [
            'id' => $this->id ?? null,
            'address' => $this->address ?? "",
            'latitude' => doubleval($this->lat ?? ""),
            'longitude' => doubleval($this->lng ?? ""),
            'is_master' => $this->is_master ?? false,
            'file_address' => $this->file_link ?? "",
        ];
    }
}
