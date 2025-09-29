<?php

namespace App\Modules\Booking\Infrastructure\Persistence\Repositories\Booking;

use Exception;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Modules\Base\Domain\DTO\BaseDTOInterface;
use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use Illuminate\Support\Collection;

class BookingRepository extends BaseRepositoryAbstract
{
    public function __construct()
    {
        $this->setModel(new Booking());
    }

    /**
     * Filter records dynamically
     */
    public function filter(
        BaseDTOInterface $dto,
        string $operator = 'like',
        array $translatableFields = [],
        $paginate = false,
        $limit = 10,
        array $whereHasRelations = [],
        array $whereHasMultipleRelations = []
    ): Collection|LengthAwarePaginator {
        try {
            $query = $this->getModel()->query();

            // البحث بالكلمة
            $query->when(isset($dto->word), function ($q) use ($dto, $translatableFields, $operator) {
                $q->where(function ($subQuery) use ($dto, $translatableFields, $operator) {
                    foreach ($translatableFields as $field) {
                        $subQuery->orWhereTranslationLike($field, "%{$dto->word}%");
                    }
                });
            });

            // فلترة باقي الحقول
            foreach ($dto->toArray() as $key => $value) {
                if (filled($value)) {
                    $query
                        ->when(in_array($key, $translatableFields), fn($q) => $q->whereTranslationLike($key, "%{$value}%"))
                        ->when(is_array($value), fn($q) => $q->whereIn($key, $value))
                        ->when(is_bool($value), fn($q) => $q->where($key, $value))
                        ->when(is_numeric($value) && !in_array($key, $translatableFields), fn($q) => $q->where($key, $operator, $value))
                        ->when(is_string($value) && !in_array($key, $translatableFields), fn($q) => $q->where($key, $operator, ($operator === 'like' ? "%{$value}%" : $value)));
                }
            }

            // فلترة العلاقات
            foreach ($whereHasRelations as $relation => $conditions) {
                $query->whereHas($relation, function ($q) use ($conditions) {
                    foreach ($conditions as $key => $values) {
                        is_array($values)
                            ? $q->whereIn($key, $values)
                            : $q->where($key, $values);
                    }
                });
            }

            foreach ($whereHasMultipleRelations as $relationsGroup) {
                foreach ($relationsGroup as $relation => $conditions) {
                    $query->whereHas($relation, function ($q) use ($conditions) {
                        foreach ($conditions as $key => $values) {
                            is_array($values)
                                ? $q->whereIn($key, $values)
                                : $q->where($key, $values);
                        }
                    });
                }
            }

            // ترتيب النتائج
            $orderBy = $dto->orderBy ?? 'id';
            $direction = $dto->direction ?? 'desc';

            return $paginate
                ? $query->orderBy($orderBy, $direction)->paginate($limit)
                : $query->orderBy($orderBy, $direction)->get();

        } catch (Exception $e) {
            report($e);
            return collect();
        }
    }

    /**
     * Check if employee is available for given time range
     */
    public function checkEmployeeAvailability($employeeId, $date, $fromHour, $toHour, $excludeBookingId = null): bool
    {
        return $this->getModel()
            ->join('booking_service', 'bookings.id', '=', 'booking_service.booking_id')
            ->where('booking_service.employee_id', $employeeId)
            ->where('bookings.status', BookingStatusEnum::CONFIRMED->value)
            ->whereDate('date', $date)
            ->when($excludeBookingId, fn($q) => $q->where('bookings.id', '!=', $excludeBookingId))
            ->where(function ($q) use ($fromHour, $toHour) {
                $q->whereBetween('from_hour', [$fromHour, $toHour])
                    ->orWhereBetween('to_hour', [$fromHour, $toHour])
                    ->orWhere(function ($q2) use ($fromHour, $toHour) {
                        $q2->where('from_hour', '<', $fromHour)
                            ->where('to_hour', '>', $toHour);
                    });
            })
            ->exists();
    }

    /**
     * Get free slots for employee in specific date
     */
    public function getAvailableTimeSlots(
        int $employeeId,
        string $date,
        string $workStart = '13:00', // 1:00 PM
        string $workEnd = '01:00',   // 1:00 AM next day
        int $intervalMinutes = 30
    ): array {
        $slots = [];
        $start = Carbon::parse("$date $workStart");
        $end = Carbon::parse("$date $workEnd");

        while ($start < $end) {
            $slotStart = $start->format('H:i:s');
            $slotEnd = $start->copy()->addMinutes($intervalMinutes)->format('H:i:s');

            if (!$this->checkEmployeeAvailability($employeeId, $date, $slotStart, $slotEnd)) {
                $slots[] = [
                    'from' => $slotStart,
                    'to' => $slotEnd,
                ];
            }

            $start->addMinutes($intervalMinutes);
        }

        return $slots;
    }
}
