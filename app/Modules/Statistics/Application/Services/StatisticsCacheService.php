<?php

namespace App\Modules\Statistics\Application\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsCacheService
{
  private const CACHE_PREFIX = 'statistics:';
  private const CACHE_TTL = 300; // 5 minutes
  private const LONG_CACHE_TTL = 1800; // 30 minutes

  /**
   * Clear all statistics cache
   */
  public function clearAllStatisticsCache(): void
  {
    $keys = [
      'dashboard',
      'top_barbers_5',
      'top_barbers_10',
      'top_services_5',
      'top_services_10',
    ];

    foreach ($keys as $key) {
      Cache::forget(self::CACHE_PREFIX . $key);
    }

    // Clear date-specific caches
    $this->clearDateSpecificCache();
  }

  /**
   * Clear date-specific caches
   */
  public function clearDateSpecificCache(): void
  {
    $today = Carbon::today()->format('Y-m-d');
    $thisMonth = Carbon::now()->format('Y-m');

    Cache::forget(self::CACHE_PREFIX . "daily_activity_{$today}");
    Cache::forget(self::CACHE_PREFIX . "monthly_activity_{$thisMonth}");
  }

  /**
   * Get cached statistics with fallback
   */
  public function getCachedStatistics(string $key, callable $callback, int $ttl = null): mixed
  {
    $cacheKey = self::CACHE_PREFIX . $key;
    $ttl = $ttl ?? self::CACHE_TTL;

    return Cache::remember($cacheKey, $ttl, $callback);
  }

  /**
   * Pre-calculate and cache dashboard statistics
   */
  public function preCalculateDashboardStats(): void
  {
    $cacheKey = self::CACHE_PREFIX . 'dashboard';

    $stats = [
      'total_bookings' => DB::table('bookings')->count(),
      'total_revenue' => $this->calculateTotalRevenue(),
      'total_employees' => DB::table('employees')
        ->where('employee_type', 'employee')
        ->where('status', 'active')
        ->count(),
      'calculated_at' => now()->toDateTimeString()
    ];

    Cache::put($cacheKey, $stats, self::LONG_CACHE_TTL);
  }

  /**
   * Pre-calculate and cache top barbers
   */
  public function preCalculateTopBarbers(int $limit = 5): void
  {
    $cacheKey = self::CACHE_PREFIX . "top_barbers_{$limit}";

    $topBarbers = DB::table('booking_service')
      ->select([
        'employees.id',
        'employees.first_name',
        'employees.last_name',
        DB::raw('COUNT(booking_service.employee_id) as bookings_count'),
        DB::raw('SUM(services.price) as total_revenue')
      ])
      ->join('services', 'booking_service.service_id', '=', 'services.id')
      ->join('employees', 'booking_service.employee_id', '=', 'employees.id')
      ->join('bookings', 'booking_service.booking_id', '=', 'bookings.id')
      ->where('employees.employee_type', 'employee')
      ->where('employees.status', 'active')
      ->where('bookings.status', 3)
      ->groupBy('employees.id', 'employees.first_name', 'employees.last_name')
      ->orderBy(DB::raw('COUNT(booking_service.employee_id)'), 'desc')
      ->limit($limit)
      ->get()
      ->map(function ($barber) {
        return [
          'id' => $barber->id,
          'name' => trim($barber->first_name . ' ' . $barber->last_name),
          'bookings_count' => $barber->bookings_count,
          'total_revenue' => $barber->total_revenue
        ];
      })->toArray();

    Cache::put($cacheKey, $topBarbers, self::LONG_CACHE_TTL);
  }

  /**
   * Calculate total revenue efficiently
   */
  private function calculateTotalRevenue(): float
  {
    return DB::table('booking_service')
      ->join('services', 'booking_service.service_id', '=', 'services.id')
      ->join('bookings', 'booking_service.booking_id', '=', 'bookings.id')
      ->where('bookings.status', 3)
      ->sum('services.price') ?? 0.0;
  }

  /**
   * Invalidate cache when booking status changes
   */
  public function invalidateCacheOnBookingChange(): void
  {
    $this->clearAllStatisticsCache();

    // Pre-calculate most used statistics
    $this->preCalculateDashboardStats();
    $this->preCalculateTopBarbers(5);
    $this->preCalculateTopBarbers(10);
  }

  /**
   * Get cache statistics
   */
  public function getCacheStats(): array
  {
    $stats = [];
    $keys = [
      'dashboard',
      'top_barbers_5',
      'top_barbers_10',
      'top_services_5',
      'top_services_10',
    ];

    foreach ($keys as $key) {
      $cacheKey = self::CACHE_PREFIX . $key;
      $stats[$key] = Cache::has($cacheKey);
    }

    return $stats;
  }
}
