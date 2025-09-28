<?php

namespace App\Modules\Booking\Infrastructure\Persistence\Repositories\BookingService;

use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Booking\Infrastructure\Persistence\Models\BookingService\BookingService;

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

    /**
     * Delete all booking services for a specific booking
     */
    public function delete(int $bookingId): bool
    {
        try {
            return (bool) $this->getModel()->where('booking_id', $bookingId)->delete();
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }
}
