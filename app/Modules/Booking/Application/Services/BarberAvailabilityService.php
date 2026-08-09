<?php

namespace App\Modules\Booking\Application\Services;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\BarberTimeOff\BarberTimeOff;
use App\Modules\Booking\Infrastructure\Persistence\Models\BarberWorkingHour\BarberWorkingHour;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BarberAvailabilityService
{
    private const WORK_START = '13:00';

    private const WORK_END = '01:00';

    private const SLOT_INTERVAL_MINUTES = 30;

    public function __construct(private readonly BookingSelectionService $selectionService) {}

    public function availableSlots(
        int $employeeId,
        string $date,
        array $serviceIds,
        ?int $excludeBookingId = null,
        array $packageIds = []
    ): array {
        $selection = $this->selectionService->resolve($serviceIds, $packageIds);

        return $this->availableSlotsForDuration($employeeId, $date, $selection['duration'], $excludeBookingId);
    }

    public function availableSlotsForDuration(
        int $employeeId,
        string $date,
        int $duration,
        ?int $excludeBookingId = null
    ): array {
        if ($duration < 1 || ! $this->isBookableBarber($employeeId) || $this->isDayOff($employeeId, $date)) {
            return [];
        }

        $workPeriod = $this->workPeriod($employeeId, $date);
        if (! $workPeriod) {
            return [];
        }
        [$workStart, $workEnd] = $workPeriod;
        $busyPeriods = $this->busyPeriods($employeeId, $date, $workStart, $workEnd, $excludeBookingId);
        $blockedPeriods = $this->blockedPeriods($employeeId, $date, $workStart, $workEnd);

        return $this->generateSlots($workStart, $workEnd, $duration, $busyPeriods->merge($blockedPeriods));
    }

    public function isAvailable(
        int $employeeId,
        string $date,
        array $serviceIds,
        string $fromHour,
        ?int $excludeBookingId = null,
        array $packageIds = []
    ): bool {
        return collect($this->availableSlots($employeeId, $date, $serviceIds, $excludeBookingId, $packageIds))
            ->contains('from_time', substr($fromHour, 0, 5));
    }

    public function isDurationAvailable(
        int $employeeId,
        string $date,
        int $duration,
        string $fromHour,
        ?int $excludeBookingId = null
    ): bool {
        return collect($this->availableSlotsForDuration($employeeId, $date, $duration, $excludeBookingId))
            ->contains('from_time', substr($fromHour, 0, 5));
    }

    public function slotStart(int $employeeId, string $date, string $fromHour): Carbon
    {
        $workPeriod = $this->workPeriod($employeeId, $date);
        if (! $workPeriod) {
            return Carbon::parse("$date $fromHour");
        }

        [$workStart, $workEnd] = $workPeriod;
        $start = Carbon::parse("$date $fromHour");

        if (! $workStart->isSameDay($workEnd) && $start->lt($workStart)) {
            $start->addDay();
        }

        return $start;
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

    private function isDayOff(int $employeeId, string $date): bool
    {
        return BarberTimeOff::query()
            ->where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->where('is_day_off', true)
            ->exists();
    }

    private function workPeriod(int $employeeId, string $date): ?array
    {
        $day = Carbon::parse($date);
        $hours = BarberWorkingHour::query()
            ->where('employee_id', $employeeId)
            ->where('weekday', $day->dayOfWeek)
            ->first();
        if ($hours?->is_day_off) {
            return null;
        }

        $fromHour = $hours?->from_hour ?? self::WORK_START;
        $toHour = $hours?->to_hour ?? self::WORK_END;
        $start = Carbon::parse("$date $fromHour");
        $end = Carbon::parse("$date $toHour");
        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return [$start, $end];
    }

    private function busyPeriods(
        int $employeeId,
        string $date,
        Carbon $workStart,
        Carbon $workEnd,
        ?int $excludeBookingId
    ): Collection {
        $periods = Booking::query()
            ->whereDate('date', $date)
            ->where(function ($query) {
                $query->where('status', BookingStatusEnum::CONFIRMED->value)
                    ->orWhere(function ($pending) {
                        $pending->where('status', BookingStatusEnum::PENDING->value)
                            ->where(fn ($expiry) => $expiry
                                ->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now()));
                    });
            })
            ->when($excludeBookingId, fn ($query) => $query->where('id', '!=', $excludeBookingId))
            ->whereHas('services', fn ($query) => $query->where('booking_service.employee_id', $employeeId))
            ->get(['id', 'date', 'from_hour', 'to_hour'])
            ->map(fn (Booking $booking) => $this->period(
                $date,
                $booking->from_hour,
                $booking->to_hour,
                $workStart,
                $workEnd
            ));

        return collect($periods->all());
    }

    private function blockedPeriods(
        int $employeeId,
        string $date,
        Carbon $workStart,
        Carbon $workEnd
    ): Collection {
        $periods = BarberTimeOff::query()
            ->where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->where('is_day_off', false)
            ->get()
            ->map(fn (BarberTimeOff $block) => $this->period(
                $date,
                $block->from_hour,
                $block->to_hour,
                $workStart,
                $workEnd
            ));

        return collect($periods->all());
    }

    private function period(
        string $date,
        string $fromHour,
        string $toHour,
        Carbon $workStart,
        Carbon $workEnd
    ): array {
        $start = Carbon::parse("$date $fromHour");
        $end = Carbon::parse("$date $toHour");

        if (! $workStart->isSameDay($workEnd) && $start->lt($workStart)) {
            $start->addDay();
            $end->addDay();
        }

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
                'from_date' => $start->toDateString(),
                'to_date' => $end->toDateString(),
                'label' => $start->format('g:i A').' - '.$end->format('g:i A'),
            ];
        }

        return $slots;
    }

    private function overlaps(Carbon $start, Carbon $end, Collection $periods): bool
    {
        return $periods->contains(fn (array $period) => $start->lt($period[1]) && $end->gt($period[0]));
    }
}
