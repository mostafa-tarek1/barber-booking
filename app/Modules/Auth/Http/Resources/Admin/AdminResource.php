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
            'first_name' => $this->first_name ?? '',
            'last_name' => $this->last_name ?? '',
            'phone' => $this->phone ?? '',
            'email' => $this->email ?? '',
            'status' => $this->status ?? '',
            'employee_type' => $this->employee_type ?? '',
            'joined_at' => $this->created_at?->toISOString(),
            'clients_served' => (int) ($this->clients_served ?? 0),
            'total_sales' => (float) ($this->total_sales ?? 0),
            'api_token' => $this->api_token ?? null,
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
