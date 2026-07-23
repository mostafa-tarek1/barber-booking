<?php

namespace App\Modules\Booking\Application\Services;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\BarberTimeOff\BarberTimeOff;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BarberAvailabilityService
{
    private const WORK_START = '13:00';
    private const WORK_END = '01:00';
    private const SLOT_INTERVAL_MINUTES = 30;

    public function availableSlots(
        int $employeeId,
        string $date,
        array $serviceIds,
        ?int $excludeBookingId = null
    ): array {
        if (!$this->isBookableBarber($employeeId)) {
            return [];
        }

        $duration = $this->serviceDuration($serviceIds);
        if ($duration === null || $this->isDayOff($employeeId, $date)) {
            return [];
        }

        [$workStart, $workEnd] = $this->workPeriod($date);
        $busyPeriods = $this->busyPeriods($employeeId, $date, $excludeBookingId);
        $blockedPeriods = $this->blockedPeriods($employeeId, $date);

        return $this->generateSlots($workStart, $workEnd, $duration, $busyPeriods->merge($blockedPeriods));
    }

    public function isAvailable(
        int $employeeId,
        string $date,
        array $serviceIds,
        string $fromHour,
        ?int $excludeBookingId = null
    ): bool {
        return collect($this->availableSlots($employeeId, $date, $serviceIds, $excludeBookingId))
            ->contains('from_time', substr($fromHour, 0, 5));
    }

    private function isBookableBarber(int $employeeId): bool
    {
        return Employee::query()
            ->whereKey($employeeId)
            ->where('employee_type', 'barber')
            ->where('status', 'active')
            ->where('is_active', true)
            ->where('is_blocked', false)
            ->exists();
    }

    private function serviceDuration(array $serviceIds): ?int
    {
        $uniqueIds = collect($serviceIds)->map(fn ($id) => (int) $id)->filter()->unique();
        $services = Service::query()->whereIn('id', $uniqueIds)->where('is_active', true)->get();

        return $services->count() === $uniqueIds->count()
            ? (int) $services->sum('duration')
            : null;
    }

    private function isDayOff(int $employeeId, string $date): bool
    {
        return BarberTimeOff::query()
            ->where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->where('is_day_off', true)
            ->exists();
    }

    private function workPeriod(string $date): array
    {
        $start = Carbon::parse("$date " . self::WORK_START);
        $end = Carbon::parse("$date " . self::WORK_END)->addDay();

        return [$start, $end];
    }

    private function busyPeriods(int $employeeId, string $date, ?int $excludeBookingId): Collection
    {
        $periods = Booking::query()
            ->whereDate('date', $date)
            ->whereIn('status', [BookingStatusEnum::PENDING->value, BookingStatusEnum::CONFIRMED->value])
            ->when($excludeBookingId, fn ($query) => $query->where('id', '!=', $excludeBookingId))
            ->whereHas('services', fn ($query) => $query->where('booking_service.employee_id', $employeeId))
            ->get(['id', 'date', 'from_hour', 'to_hour'])
            ->map(fn (Booking $booking) => $this->period($date, $booking->from_hour, $booking->to_hour));

        return collect($periods->all());
    }

    private function blockedPeriods(int $employeeId, string $date): Collection
    {
        $periods = BarberTimeOff::query()
            ->where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->where('is_day_off', false)
            ->get()
            ->map(fn (BarberTimeOff $block) => $this->period($date, $block->from_hour, $block->to_hour));

        return collect($periods->all());
    }

    private function period(string $date, string $fromHour, string $toHour): array
    {
        $start = Carbon::parse("$date $fromHour");
        $end = Carbon::parse("$date $toHour");
        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return [$start, $end];
    }

    private function generateSlots(
        Carbon $workStart,
        Carbon $workEnd,
        int $duration,
        Collection $unavailablePeriods
    ): array {
        $slots = [];
        for ($start = $workStart->copy(); $start->copy()->addMinutes($duration)->lte($workEnd); $start->addMinutes(self::SLOT_INTERVAL_MINUTES)) {
            $end = $start->copy()->addMinutes($duration);
            if ($start->isPast() || $this->overlaps($start, $end, $unavailablePeriods)) {
                continue;
            }
            $slots[] = [
                'from_time' => $start->format('H:i'),
                'to_time' => $end->format('H:i'),
                'label' => $start->format('g:i A') . ' - ' . $end->format('g:i A'),
            ];
        }

        return $slots;
    }

    private function overlaps(Carbon $start, Carbon $end, Collection $periods): bool
    {
        return $periods->contains(fn (array $period) => $start->lt($period[1]) && $end->gt($period[0]));
    }
}
