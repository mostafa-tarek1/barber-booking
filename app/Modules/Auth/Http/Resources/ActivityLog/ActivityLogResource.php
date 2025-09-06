<?php
namespace App\Modules\Auth\Http\Resources\ActivityLog;
use Illuminate\Http\Resources\Json\JsonResource;
class ActivityLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'description'  => $this->description,
            'causer'       => $this->causer ? [
                'id'    => $this->causer->id,
                'name'  => $this->causer->name,
                'email' => $this->causer->email,
            ] : null,
            'subject_type' => class_basename($this->subject_type),
            'subject_id'   => $this->subject_id,
            'properties'   => $this->properties,
            'created_at'   => $this->created_at->toDateTimeString(),
        ];
    }
}
