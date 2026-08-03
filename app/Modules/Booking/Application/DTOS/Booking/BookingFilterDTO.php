<?php

namespace App\Modules\Booking\Application\DTOS\Booking;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class BookingFilterDTO extends BaseDTOAbstract
{
    protected bool $excludeAttributes = true;

    public $booking_id;
    public $name;
    public $phone;
    public $email;
    public $date;
    public $date_from;
    public $date_to;
    public $status;
    public $search;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function excludedAttributes(): array
    {
        return [
            'excludeAttributes',
            'search',
            'date',
            'date_from',
            'date_to',
            'status',
        ];
    }
}
