<?php

namespace App\Modules\BookingService\Infrastructure\Persistence\Repositories\BookingService;

use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\BookingService;
use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;

class BookingServiceRepository extends BaseRepositoryAbstract
{
    public function __construct()
    {
        $this->setModel(new BookingService());
    }

    /**
     * رجّع كل الخدمات الخاصة بحجز معين
     */
    public function getByBooking(int $bookingId)
    {
        return $this->getModel()->where('booking_id', $bookingId)->get();
    }

    /**
     * رجّع كل الحجوزات اللي مرتبطة بموظف (حلاق) معين
     */
    public function getByEmployee(int $employeeId)
    {
        return $this->getModel()->where('employee_id', $employeeId)->get();
    }
}
