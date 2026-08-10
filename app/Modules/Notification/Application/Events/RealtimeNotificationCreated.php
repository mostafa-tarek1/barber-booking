<?php

namespace App\Modules\Notification\Application\Events;

use App\Modules\Notification\Infrastructure\Persistence\Models\Notification\Notification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Broadcasting\ShouldRescue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RealtimeNotificationCreated implements ShouldBroadcastNow, ShouldRescue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Notification $notification) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(
            "employees.{$this->notification->employee_id}.notifications"
        );
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    public function broadcastWith(): array
    {
        return [
            'notification' => [
                'id' => $this->notification->id,
                'title' => $this->notification->title,
                'message' => $this->notification->message,
                'type' => $this->notification->type,
                'icon' => $this->notification->icon,
                'color' => $this->notification->color,
                'data' => $this->notification->data,
                'is_read' => $this->notification->is_read,
                'employee_id' => $this->notification->employee_id,
                'created_at' => $this->notification->created_at->format('Y-m-d H:i:s'),
                'read_at' => null,
            ],
        ];
    }
}
