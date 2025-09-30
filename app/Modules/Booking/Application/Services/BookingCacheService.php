<?php

namespace App\Modules\Booking\Application\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class BookingCacheService
{
  private const CACHE_PREFIX = 'bookings:';
  private const CACHE_TTL = 300; // 5 minutes

  /**
   * Get cached bookings
   */
  public function getCachedBookings(string $key, callable $callback)
  {
    $cacheKey = self::CACHE_PREFIX . $key;

    return Cache::remember($cacheKey, self::CACHE_TTL, $callback);
  }

  /**
   * Cache booking statistics
   */
  public function cacheBookingStats(array $stats): void
  {
    $cacheKey = self::CACHE_PREFIX . 'stats:dashboard';
    Cache::put($cacheKey, $stats, self::CACHE_TTL);
  }

  /**
   * Get cached booking statistics
   */
  public function getCachedBookingStats(): ?array
  {
    $cacheKey = self::CACHE_PREFIX . 'stats:dashboard';
    return Cache::get($cacheKey);
  }

  /**
   * Cache booking details
   */
  public function cacheBookingDetails(int $bookingId, array $details): void
  {
    $cacheKey = self::CACHE_PREFIX . 'details:' . $bookingId;
    Cache::put($cacheKey, $details, self::CACHE_TTL);
  }

  /**
   * Get cached booking details
   */
  public function getCachedBookingDetails(int $bookingId): ?array
  {
    $cacheKey = self::CACHE_PREFIX . 'details:' . $bookingId;
    return Cache::get($cacheKey);
  }

  /**
   * Clear booking cache
   */
  public function clearBookingCache(int $bookingId = null): void
  {
    if ($bookingId) {
      // Clear specific booking cache
      $keys = [
        self::CACHE_PREFIX . 'details:' . $bookingId,
        self::CACHE_PREFIX . 'stats:dashboard',
      ];

      foreach ($keys as $key) {
        Cache::forget($key);
      }
    } else {
      // Clear all booking cache
      $pattern = self::CACHE_PREFIX . '*';

      if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
        $keys = Redis::keys($pattern);
        if (!empty($keys)) {
          Redis::del($keys);
        }
      } else {
        // For file cache, we can't easily clear by pattern
        // So we'll clear common keys
        $commonKeys = [
          'stats:dashboard',
          'list:dashboard',
          'list:api',
        ];

        foreach ($commonKeys as $key) {
          Cache::forget(self::CACHE_PREFIX . $key);
        }
      }
    }
  }

  /**
   * Generate cache key for bookings list
   */
  public function generateListCacheKey(array $filters): string
  {
    $key = 'list:' . md5(serialize($filters));
    return $key;
  }

  /**
   * Cache employee availability
   */
  public function cacheEmployeeAvailability(int $employeeId, string $date, array $slots): void
  {
    $cacheKey = self::CACHE_PREFIX . 'availability:' . $employeeId . ':' . $date;
    Cache::put($cacheKey, $slots, 60); // 1 minute cache for availability
  }

  /**
   * Get cached employee availability
   */
  public function getCachedEmployeeAvailability(int $employeeId, string $date): ?array
  {
    $cacheKey = self::CACHE_PREFIX . 'availability:' . $employeeId . ':' . $date;
    return Cache::get($cacheKey);
  }
}
