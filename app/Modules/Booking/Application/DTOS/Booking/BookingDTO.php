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

    public $expires_at;

    public $created_by;

    public $updated_by;

    public $service_id;

    public $services;

    public $packages;

    public $employee_id;

    public $notes;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
