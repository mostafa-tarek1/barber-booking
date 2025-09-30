<?php

namespace App\Modules\Notification\Application\Events;

use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingCancelled
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public Booking $booking;

  /**
   * Create a new event instance.
   */
  public function __construct(Booking $booking)
  {
    $this->booking = $booking;
  }
}
