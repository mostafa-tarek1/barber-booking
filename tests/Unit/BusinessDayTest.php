<?php

namespace Tests\Unit;

use App\Support\BusinessDay;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BusinessDayTest extends TestCase
{
    public function test_business_day_starts_at_four_am_cairo_time(): void
    {
        [$start, $end] = BusinessDay::range('2026-07-30');

        $this->assertSame('2026-07-30 04:00:00', $start->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-31 04:00:00', $end->format('Y-m-d H:i:s'));
        $this->assertSame('Africa/Cairo', $start->timezoneName);
    }

    #[DataProvider('businessDayBoundaryProvider')]
    public function test_moment_is_assigned_to_the_correct_business_day(string $moment, string $expectedDate): void
    {
        $cairoMoment = Carbon::parse($moment, 'Africa/Cairo');

        $this->assertSame($expectedDate, BusinessDay::dateFor($cairoMoment));
    }

    public static function businessDayBoundaryProvider(): array
    {
        return [
            'last second of previous business day' => ['2026-07-31 03:59:59', '2026-07-30'],
            'first second of new business day' => ['2026-07-31 04:00:00', '2026-07-31'],
        ];
    }
}
