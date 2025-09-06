<?php

namespace App\Modules\Auth\Http\Resources\Admin;
use App\Modules\Auth\Http\Resources\Permission\PermissionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name ?? "",
            'last_name' => $this->last_name ?? "",
            'phone' => $this->phone ?? "",
            'email' => $this->email ?? "",
            'api_token' => $this->api_token ?? null,
            'is_email_verified' => (bool) $this->is_email_verified,
            'email_verified_at' => $this->email_verified_at,
            'is_phone_verified' => (bool) $this->is_phone_verified,
            'phone_verified_at' => $this->phone_verified_at,
            'image' => $this->image_link,

            'parent_id' => $this->parent_id,

            'parent' => $this->parent ? [
                'id' => $this->parent->id,
                'first_name' => $this->parent->first_name,
                'last_name' => $this->parent->last_name,
            ] : null,

            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
        ];
    }
}

