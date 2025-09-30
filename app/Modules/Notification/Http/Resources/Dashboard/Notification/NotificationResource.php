<?php

namespace App\Modules\Notification\Http\Resources\Dashboard\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'title' => $this->title,
      'message' => $this->message,
      'type' => $this->type,
      'icon' => $this->icon,
      'color' => $this->color,
      'data' => $this->data,
      'is_read' => $this->is_read,
      'is_for_employee' => $this->is_for_employee,
      'is_for_user' => $this->is_for_user,
      'recipient_type' => $this->recipient_type,
      'recipient_name' => $this->recipient_name,
      'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
      'read_at' => $this->read_at?->format('Y-m-d H:i:s'),
      'created_by' => $this->createdBy ? [
        'id' => $this->createdBy->id,
        'name' => trim($this->createdBy->first_name . ' ' . $this->createdBy->last_name),
        'email' => $this->createdBy->email,
      ] : null,
    ];
  }
}
