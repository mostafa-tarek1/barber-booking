<?php

namespace App\Modules\Booking\Application\DTOS\BookingService;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class BookingServiceDTO extends BaseDTOAbstract
{
    public $booking_id;
    public $service_id;
    public $employee_id;


    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
