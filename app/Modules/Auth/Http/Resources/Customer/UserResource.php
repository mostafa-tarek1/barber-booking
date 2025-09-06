<?php

namespace App\Modules\Auth\Http\Resources\Customer;

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
        $token = ($request->bearerToken()) ? $request->bearerToken() : '';
        $international_address = $user->getEntity()->internationalAddress();
        $locations = $user->addresses()->where('is_master', false)->orderBy('id', 'desc')->get();
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
            'international_address' => ($international_address) ? new UserAddressResource($international_address) : (object) [],
            'commercial_register' => $user->commercial_register_link ?? "",
            'tax_register' => $user->tax_register_link ?? "",
            "phone_code" => $user->phone_code ?? null,
            'api_token' => $user->api_token ?? $token,
            'is_complete_data' => $user->getEntity()->isCompleteData() ?? false,
            'is_approved' => $user->is_approved ?? false,
            'is_verified' => $user->is_phone_verified ?? false,
            'location' => UserAddressResource::collection($locations),
            // 'status' => $user->status ?? null,
            // 'is_email_verified' => $user->is_email_verified ?? null,
            // 'email_verified_at' => $user->email_verified_at ?? null,
            // 'is_phone_verified' => $user->is_phone_verified ?? null,
            // 'phone_verified_at' => $user->phone_verified_at ?? null,
            // 'stage' => $user->stage ?? null,
            // 'location' => $user->location ?? null,
            // 'nationality' => $user->nationality ?? null,
            // 'tax_register' => $user->tax_register_link ?? "",
            "identify_number" => $this?->identify_number ?? "",
            'is_blocked' => (bool)$user->is_blocked,
            'allow_notification' => $user->allow_notification ?? true,
        ];
    }
}
