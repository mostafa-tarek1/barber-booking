<?php

namespace App\Modules\Statistics\Application\UseCases\Statistics;

use App\Modules\Base\Application\Response\DataSuccess;
use App\Modules\Base\Application\Response\DataFailed;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Statistics\Application\DTOS\Statistics\StatisticsDTO;
use App\Modules\Statistics\Application\DTOS\Statistics\DailyActivityDTO;
use App\Modules\Statistics\Application\DTOS\Statistics\MonthlyActivityDTO;
use App\Modules\Statistics\Application\DTOS\Statistics\TopServiceDTO;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Infrastructure\Persistence\Models\BookingService\BookingService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsUseCase
{
    /**
     * جلب الإحصائيات الرئيسية للداشبورد
     */
    public function getDashboardStatistics(): DataStatus
    {
        try {
            // إجمالي الحجوزات
            $totalBookings = Booking::count();

            // إجمالي الإيرادات
            $totalRevenue = $this->calculateTotalRevenue();

            // إجمالي الموظفين (الحلاقين)
            $totalEmployees = Employee::where('employee_type', 'employee')
                ->where('status', 'active')
                ->count();

            $statistics = [
                'total_bookings' => $totalBookings,
                'total_revenue' => $totalRevenue,
                'total_employees' => $totalEmployees
            ];

            return new DataSuccess(
                $statistics,
                null,
                [],
                true,
                200,
                'تم جلب الإحصائيات بنجاح'
            );
        } catch (\Exception $e) {
            return new DataFailed(
                ['error' => $e->getMessage()],
                false,
                500,
                'حدث خطأ في جلب الإحصائيات'
            );
        }
    }

    /**
     * جلب النشاط اليومي
     */
    public function getDailyActivity(string $date): DataStatus
    {
        try {
            $startDate = Carbon::parse($date)->startOfDay();
            $endDate = Carbon::parse($date)->endOfDay();

            // الحجوزات اليومية
            $dailyBookings = Booking::whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as bookings_count'),
                    DB::raw('SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as confirmed_bookings'),
                    DB::raw('SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as cancelled_bookings'),
                    DB::raw('SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as finished_bookings')
                )
                ->groupBy(DB::raw('DATE(created_at)'))
                ->first();

            // الإيرادات اليومية
            $dailyRevenue = $this->calculateDailyRevenue($date);

            $dailyActivity = [
                'date' => $date,
                'bookings' => $dailyBookings ? [
                    'total' => $dailyBookings->bookings_count ?? 0,
                    'confirmed' => $dailyBookings->confirmed_bookings ?? 0,
                    'cancelled' => $dailyBookings->cancelled_bookings ?? 0,
                    'finished' => $dailyBookings->finished_bookings ?? 0,
                ] : [
                    'total' => 0,
                    'confirmed' => 0,
                    'cancelled' => 0,
                    'finished' => 0,
                ],
                'revenue' => $dailyRevenue
            ];

            return new DataSuccess(
                $dailyActivity,
                null,
                [],
                true,
                200,
                'تم جلب النشاط اليومي بنجاح'
            );
        } catch (\Exception $e) {
            return new DataFailed(
                ['error' => $e->getMessage()],
                false,
                500,
                'حدث خطأ في جلب النشاط اليومي'
            );
        }
    }

    /**
     * جلب النشاط الشهري
     */
    public function getMonthlyActivity(string $month): DataStatus
    {
        try {
            $startDate = Carbon::parse($month . '-01')->startOfMonth();
            $endDate = Carbon::parse($month . '-01')->endOfMonth();

            // الحجوزات الشهرية حسب اليوم
            $monthlyBookings = Booking::whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as bookings_count'),
                    DB::raw('SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as confirmed_bookings'),
                    DB::raw('SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as cancelled_bookings'),
                    DB::raw('SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as finished_bookings')
                )
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy(DB::raw('DATE(created_at)'))
                ->get();

            // الإيرادات الشهرية
            $monthlyRevenue = $this->calculateMonthlyRevenue($month);

            $monthlyActivity = [
                'month' => $month,
                'daily_bookings' => $monthlyBookings->map(function ($item) {
                    return [
                        'date' => $item->date,
                        'total' => $item->bookings_count,
                        'confirmed' => $item->confirmed_bookings,
                        'cancelled' => $item->cancelled_bookings,
                        'finished' => $item->finished_bookings,
                    ];
                })->toArray(),
                'total_revenue' => $monthlyRevenue
            ];

            return new DataSuccess(
                $monthlyActivity,
                null,
                [],
                true,
                200,
                'تم جلب النشاط الشهري بنجاح'
            );
        } catch (\Exception $e) {
            return new DataFailed(
                ['error' => $e->getMessage()],
                false,
                500,
                'حدث خطأ في جلب النشاط الشهري'
            );
        }
    }

    /**
     * جلب أفضل الخدمات
     */
    public function getTopServices(int $limit = 5): DataStatus
    {
        try {
            $topServices = BookingService::select(
                'services.id',
                'services.price',
                DB::raw('COUNT(booking_service.service_id) as bookings_count'),
                DB::raw('SUM(services.price) as total_revenue'),
                'service_translations.name as service_name'
            )
                ->join('services', 'booking_service.service_id', '=', 'services.id')
                ->join('bookings', 'booking_service.booking_id', '=', 'bookings.id')
                ->join('service_translations', function ($join) {
                    $join->on('services.id', '=', 'service_translations.service_id')
                        ->where('service_translations.locale', '=', app()->getLocale());
                })
                ->where('bookings.status', 3) // الحجوزات المكتملة فقط
                ->groupBy('services.id', 'services.price', 'service_translations.name')
                ->orderBy(DB::raw('COUNT(booking_service.service_id)'), 'desc')
                ->limit($limit)
                ->get();

            $topServicesData = $topServices->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->service_name,
                    'price' => $service->price,
                    'bookings_count' => $service->bookings_count,
                    'total_revenue' => $service->total_revenue
                ];
            })->toArray();

            return new DataSuccess(
                $topServicesData,
                null,
                [],
                true,
                200,
                'تم جلب أفضل الخدمات بنجاح'
            );
        } catch (\Exception $e) {
            return new DataFailed(
                ['error' => $e->getMessage()],
                false,
                500,
                'حدث خطأ في جلب أفضل الخدمات'
            );
        }
    }

    /**
     * حساب إجمالي الإيرادات
     */
    private function calculateTotalRevenue(): float
    {
        $totalRevenue = BookingService::select(
            DB::raw('SUM(services.price) as total_revenue')
        )
            ->join('services', 'booking_service.service_id', '=', 'services.id')
            ->join('bookings', 'booking_service.booking_id', '=', 'bookings.id')
            ->where('bookings.status', 3) // الحجوزات المكتملة فقط
            ->value('total_revenue');

        return $totalRevenue ?? 0.0;
    }

    /**
     * حساب الإيرادات اليومية
     */
    private function calculateDailyRevenue(string $date): float
    {
        $startDate = Carbon::parse($date)->startOfDay();
        $endDate = Carbon::parse($date)->endOfDay();

        $dailyRevenue = BookingService::select(
            DB::raw('SUM(services.price) as total_revenue')
        )
            ->join('services', 'booking_service.service_id', '=', 'services.id')
            ->join('bookings', 'booking_service.booking_id', '=', 'bookings.id')
            ->where('bookings.status', 3) // الحجوزات المكتملة فقط
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->value('total_revenue');

        return $dailyRevenue ?? 0.0;
    }

    /**
     * جلب أفضل الحلاقين
     */
    public function getTopBarbers(int $limit = 5): DataStatus
    {
        try {
            $topBarbers = BookingService::select(
                'employees.id',
                'employees.first_name',
                'employees.last_name',
                DB::raw('COUNT(booking_service.employee_id) as bookings_count'),
                DB::raw('SUM(services.price) as total_revenue')
            )
                ->join('services', 'booking_service.service_id', '=', 'services.id')
                ->join('employees', 'booking_service.employee_id', '=', 'employees.id')
                ->join('bookings', 'booking_service.booking_id', '=', 'bookings.id')
                ->where('employees.employee_type', 'employee')
                ->where('employees.status', 'active')
                ->where('bookings.status', 3) // الحجوزات المكتملة فقط
                ->groupBy('employees.id', 'employees.first_name', 'employees.last_name')
                ->orderBy(DB::raw('COUNT(booking_service.employee_id)'), 'desc')
                ->limit($limit)
                ->get();

            $topBarbersData = $topBarbers->map(function ($barber) {
                return [
                    'id' => $barber->id,
                    'name' => trim($barber->first_name . ' ' . $barber->last_name),
                    'bookings_count' => $barber->bookings_count,
                    'total_revenue' => $barber->total_revenue
                ];
            })->toArray();

            return new DataSuccess(
                $topBarbersData,
                null,
                [],
                true,
                200,
                'تم جلب أفضل الحلاقين بنجاح'
            );
        } catch (\Exception $e) {
            return new DataFailed(
                ['error' => $e->getMessage()],
                false,
                500,
                'حدث خطأ في جلب أفضل الحلاقين'
            );
        }
    }

    /**
     * حساب الإيرادات الشهرية
     */
    private function calculateMonthlyRevenue(string $month): float
    {
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        $monthlyRevenue = BookingService::select(
            DB::raw('SUM(services.price) as total_revenue')
        )
            ->join('services', 'booking_service.service_id', '=', 'services.id')
            ->join('bookings', 'booking_service.booking_id', '=', 'bookings.id')
            ->where('bookings.status', 3) // الحجوزات المكتملة فقط
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->value('total_revenue');

        return $monthlyRevenue ?? 0.0;
    }
}
