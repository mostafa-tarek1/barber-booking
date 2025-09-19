<?php

namespace App\Modules\Booking\Application\DTOS\Booking;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class BookingDTO extends BaseDTOAbstract
{
    public $booking_id;
    public $name;
    public $phone;
    public $email;
    public $date;
    public $from_hour;
    public $to_hour;
    public $status;
    public $created_by;
    public $updated_by;
    // public $rooms;

    public $room_id;
    public $additionals;


    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
