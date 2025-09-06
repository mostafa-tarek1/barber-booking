<?php

namespace App\Modules\Auth\Http\Resources\Permission;


use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class PermissionResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'permission' => $this->permission,
        ];
    }
}
