<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

final class BusinessDay
{
    private const START_HOUR = 4;

    private const TIMEZONE = 'Africa/Cairo';

    public static function currentDate(?CarbonInterface $moment = null): string
    {
        return self::dateFor($moment ?? now(self::TIMEZONE));
    }

    public static function dateFor(CarbonInterface $moment): string
    {
        $cairoMoment = Carbon::instance($moment)->setTimezone(self::TIMEZONE);

        if ($cairoMoment->hour < self::START_HOUR) {
            $cairoMoment->subDay();
        }

        return $cairoMoment->toDateString();
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function range(string $date): array
    {
        $start = Carbon::parse($date, self::TIMEZONE)
            ->startOfDay()
            ->addHours(self::START_HOUR);

        return [$start, $start->copy()->addDay()];
    }

    public static function appointmentAt(string $date, string $time): Carbon
    {
        $appointment = Carbon::parse("{$date} {$time}", self::TIMEZONE);

        if ($appointment->hour < self::START_HOUR) {
            $appointment->addDay();
        }

        return $appointment;
    }
}
