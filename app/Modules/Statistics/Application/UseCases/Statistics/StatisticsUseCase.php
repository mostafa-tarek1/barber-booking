<?php

namespace App\Modules\Statistics\Application\UseCases\Statistics;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Base\Application\Response\DataFailed;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Base\Application\Response\DataSuccess;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Booking\Infrastructure\Persistence\Models\Invoice\Invoice;
use App\Support\BusinessDay;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticsUseCase
{
    public function getDashboardStatistics(): DataStatus
    {
        try {
            $today = now('Africa/Cairo')->toDateString();
            $salesDate = BusinessDay::currentDate();
            $todayRange = BusinessDay::range($salesDate);

            return new DataSuccess([
                'total_bookings' => Booking::query()->count(),
                'total_revenue' => (float) Invoice::query()->where('status', 'paid')->sum('total'),
                'total_employees' => Employee::query()->where('employee_type', 'barber')->where('status', 'active')->count(),
                'today_bookings' => Booking::query()->whereDate('date', $today)->count(),
                'today_revenue' => $this->paidRevenueBetween(...$todayRange),
                'total_paid_invoices' => Invoice::query()->where('status', 'paid')->count(),
            ], message: 'تم جلب الإحصائيات بنجاح');
        } catch (\Throwable $exception) {
            report($exception);

            return new DataFailed(['error' => $exception->getMessage()], false, 500, 'حدث خطأ في جلب الإحصائيات');
        }
    }

    public function getDailyActivity(string $date): DataStatus
    {
        try {
            $bookingCounts = Booking::query()
                ->whereDate('date', $date)
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as confirmed')
                ->selectRaw('SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as cancelled')
                ->selectRaw('SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as finished')
                ->first();

            return new DataSuccess([
                'date' => $date,
                'bookings' => [
                    'total' => (int) ($bookingCounts->total ?? 0),
                    'confirmed' => (int) ($bookingCounts->confirmed ?? 0),
                    'cancelled' => (int) ($bookingCounts->cancelled ?? 0),
                    'finished' => (int) ($bookingCounts->finished ?? 0),
                ],
                'revenue' => $this->paidRevenueBetween(...BusinessDay::range($date)),
            ], message: 'تم جلب النشاط اليومي بنجاح');
        } catch (\Throwable $exception) {
            report($exception);

            return new DataFailed(['error' => $exception->getMessage()], false, 500, 'حدث خطأ في جلب النشاط اليومي');
        }
    }

    public function getMonthlyActivity(string $month): DataStatus
    {
        try {
            $monthStart = Carbon::createFromFormat('Y-m-d', "$month-01", 'Africa/Cairo')->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            [$revenueStart] = BusinessDay::range($monthStart->toDateString());
            [, $revenueEnd] = BusinessDay::range($monthEnd->toDateString());

            return new DataSuccess([
                'month' => $month,
                'daily_bookings' => $this->monthlyBookingRows($monthStart, $monthEnd),
                'total_revenue' => $this->paidRevenueBetween($revenueStart, $revenueEnd),
                'recent_daily_activity' => $this->recentDailyActivity(),
                'monthly_revenue' => $this->lastSixMonthsRevenue(),
            ], message: 'تم جلب النشاط الشهري بنجاح');
        } catch (\Throwable $exception) {
            report($exception);

            return new DataFailed(['error' => $exception->getMessage()], false, 500, 'حدث خطأ في جلب النشاط الشهري');
        }
    }

    public function getTopServices(int $limit = 5): DataStatus
    {
        try {
            $services = DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->leftJoin('service_translations', function ($join) {
                    $join->on('invoice_items.service_id', '=', 'service_translations.service_id')
                        ->where('service_translations.locale', app()->getLocale());
                })
                ->where('invoices.status', 'paid')
                ->whereNotNull('invoice_items.service_id')
                ->selectRaw('invoice_items.service_id as id')
                ->selectRaw('COALESCE(MAX(service_translations.name), MAX(invoice_items.description)) as name')
                ->selectRaw('MAX(invoice_items.unit_price) as price')
                ->selectRaw('SUM(invoice_items.quantity) as bookings_count')
                ->selectRaw('SUM(invoice_items.total) as total_revenue')
                ->groupBy('invoice_items.service_id')
                ->orderByDesc('bookings_count')
                ->limit($limit)
                ->get();

            return new DataSuccess($services, message: 'تم جلب أفضل الخدمات بنجاح');
        } catch (\Throwable $exception) {
            report($exception);

            return new DataFailed(['error' => $exception->getMessage()], false, 500, 'حدث خطأ في جلب أفضل الخدمات');
        }
    }

    public function getTopBarbers(int $limit = 5): DataStatus
    {
        try {
            $barbers = DB::table('employees')
                ->join('invoices', 'invoices.barber_id', '=', 'employees.id')
                ->where('employees.employee_type', 'barber')
                ->where('invoices.status', 'paid')
                ->select(['employees.id', 'employees.first_name', 'employees.last_name'])
                ->selectRaw('COUNT(invoices.id) as bookings_count')
                ->selectRaw('SUM(invoices.total) as total_revenue')
                ->groupBy('employees.id', 'employees.first_name', 'employees.last_name')
                ->orderByDesc('total_revenue')
                ->limit($limit)
                ->get()
                ->map(fn (object $barber) => [
                    'id' => $barber->id,
                    'name' => trim("$barber->first_name $barber->last_name"),
                    'bookings_count' => (int) $barber->bookings_count,
                    'total_revenue' => $barber->total_revenue,
                ]);

            return new DataSuccess($barbers, message: 'تم جلب أفضل الحلاقين بنجاح');
        } catch (\Throwable $exception) {
            report($exception);

            return new DataFailed(['error' => $exception->getMessage()], false, 500, 'حدث خطأ في جلب أفضل الحلاقين');
        }
    }

    private function monthlyBookingRows(Carbon $monthStart, Carbon $monthEnd): array
    {
        return Booking::query()
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->selectRaw('DATE(date) as date, COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as confirmed')
            ->selectRaw('SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as cancelled')
            ->selectRaw('SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as finished')
            ->groupByRaw('DATE(date)')
            ->orderBy('date')
            ->get()
            ->map(fn (object $row) => [
                'date' => Carbon::parse($row->date)->toDateString(),
                'total' => (int) $row->total,
                'confirmed' => (int) $row->confirmed,
                'cancelled' => (int) $row->cancelled,
                'finished' => (int) $row->finished,
            ])
            ->all();
    }

    private function recentDailyActivity(): array
    {
        $end = Carbon::parse(BusinessDay::currentDate(), 'Africa/Cairo')->startOfDay();
        $start = $end->copy()->subDays(6);
        $bookings = Booking::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('DATE(date) as date, COUNT(*) as total')
            ->groupByRaw('DATE(date)')
            ->pluck('total', 'date');
        [$revenueStart] = BusinessDay::range($start->toDateString());
        [, $revenueEnd] = BusinessDay::range($end->toDateString());
        $revenues = $this->revenueGroupedByDate($revenueStart, $revenueEnd);

        return collect(range(0, 6))->map(function (int $dayOffset) use ($start, $bookings, $revenues) {
            $date = $start->copy()->addDays($dayOffset)->toDateString();

            return [
                'date' => $date,
                'bookings' => (int) ($bookings[$date] ?? 0),
                'revenue' => (float) ($revenues[$date] ?? 0),
            ];
        })->all();
    }

    private function lastSixMonthsRevenue(): array
    {
        $currentBusinessDate = Carbon::parse(BusinessDay::currentDate(), 'Africa/Cairo');
        $firstMonth = $currentBusinessDate->copy()->startOfMonth()->subMonths(5);
        $lastMonth = $currentBusinessDate->copy()->endOfMonth();
        [$revenueStart] = BusinessDay::range($firstMonth->toDateString());
        [, $revenueEnd] = BusinessDay::range($lastMonth->toDateString());
        $revenues = Invoice::query()
            ->where('status', 'paid')
            ->where('paid_at', '>=', $revenueStart)
            ->where('paid_at', '<', $revenueEnd)
            ->get(['paid_at', 'total'])
            ->groupBy(fn (Invoice $invoice) => substr(BusinessDay::dateFor($invoice->paid_at), 0, 7))
            ->map(fn ($invoices) => $invoices->sum('total'));

        return collect(range(0, 5))->map(function (int $monthOffset) use ($firstMonth, $revenues) {
            $month = $firstMonth->copy()->addMonths($monthOffset)->format('Y-m');

            return ['month' => $month, 'revenue' => (float) ($revenues[$month] ?? 0)];
        })->all();
    }

    private function revenueGroupedByDate(Carbon $start, Carbon $end)
    {
        return Invoice::query()
            ->where('status', 'paid')
            ->where('paid_at', '>=', $start)
            ->where('paid_at', '<', $end)
            ->get(['paid_at', 'total'])
            ->groupBy(fn (Invoice $invoice) => BusinessDay::dateFor($invoice->paid_at))
            ->map(fn ($invoices) => $invoices->sum('total'));
    }

    private function paidRevenueBetween(Carbon $start, Carbon $end): float
    {
        return (float) Invoice::query()
            ->where('status', 'paid')
            ->where('paid_at', '>=', $start)
            ->where('paid_at', '<', $end)
            ->sum('total');
    }
}
