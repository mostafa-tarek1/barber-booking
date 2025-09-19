<?php

namespace App\Modules\Booking\Application\DTOS\BookingRooms;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class BookingRoomsDTO extends BaseDTOAbstract
{
    public $booking_id;
    public $room_id;
    public $room_additional_id;


    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
