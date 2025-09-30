<?php

namespace App\Modules\Notification\Application\Events;

use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingCreated
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public Booking $booking;
  public array $services;
  public int $employeeId;

  /**
   * Create a new event instance.
   */
  public function __construct(Booking $booking, array $services, int $employeeId)
  {
    $this->booking = $booking;
    $this->services = $services;
    $this->employeeId = $employeeId;
  }
}
