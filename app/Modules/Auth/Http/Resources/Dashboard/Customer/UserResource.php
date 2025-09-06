<?php

namespace App\Modules\Auth\Http\Resources\Dashboard\Customer;

use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\User;
use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\UserAddress\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;
        return [
            'id' => $user->id ?? null,
            'name' => $user->name ?? "",
            // 'first_name' => $user->first_name ?? "",
            // 'last_name' => $user->last_name ?? "",
                        "created_at" => $this->created_at->toDateTimeString(),
            'username' => $user->username ?? "",
            'email' => $user->email ?? null,
            'phone' => $user->phone ?? null,
            'image' => $user->image_link ?? "",
            "phone_code" => $user->phone_code ?? null,
            'is_complete_data' => $user->getEntity()->isCompleteData() ?? false,
            'is_approved' => $user->is_approved ?? false,
            'is_verified' => $user->is_phone_verified ?? false,
            "identify_number" => $this?->identify_number ?? "",
            'is_blocked' => (bool)$user->is_blocked,
            'created_at' => $user->created_at->toDateTimeString(),
            'allow_notification' => $user->allow_notification ?? true,
        ];
    }
}
