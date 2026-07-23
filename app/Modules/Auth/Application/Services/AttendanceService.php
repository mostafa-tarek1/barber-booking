<?php

namespace App\Modules\Auth\Application\Services;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Auth\Infrastructure\Persistence\Models\Attendance\AttendanceRecord;
use App\Modules\Auth\Infrastructure\Persistence\Models\Attendance\AttendanceSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function punch(Employee $employee, string $action, array $position): AttendanceRecord
    {
        $setting = AttendanceSetting::query()->latest('id')->first();
        if (! $setting) {
            throw ValidationException::withMessages(['location' => 'لم يتم تحديد موقع الفرع بعد. تواصل مع الأدمن.']);
        }

        $distance = $this->distanceInMeters(
            (float) $setting->latitude,
            (float) $setting->longitude,
            $position['latitude'],
            $position['longitude']
        );

        if ($distance > $setting->allowed_radius_meters) {
            throw ValidationException::withMessages([
                'location' => sprintf(
                    'أنت خارج نطاق الفرع. المسافة الحالية %d متر، والمسموح %d متر.',
                    (int) round($distance),
                    $setting->allowed_radius_meters
                ),
            ]);
        }

        return DB::transaction(function () use ($employee, $action, $position, $distance) {
            $today = now('Africa/Cairo')->toDateString();
            $record = AttendanceRecord::query()
                ->where('employee_id', $employee->id)
                ->whereDate('attendance_date', $today)
                ->lockForUpdate()
                ->first();

            if ($action === 'check_in') {
                if ($record) {
                    throw ValidationException::withMessages(['action' => 'تم تسجيل الحضور اليوم بالفعل.']);
                }

                return AttendanceRecord::create([
                    'employee_id' => $employee->id,
                    'attendance_date' => $today,
                    'checked_in_at' => now(),
                    'check_in_latitude' => $position['latitude'],
                    'check_in_longitude' => $position['longitude'],
                    'check_in_distance_meters' => round($distance, 2),
                    'check_in_accuracy_meters' => $position['accuracy'],
                ])->load('employee:id,first_name,last_name,employee_type');
            }

            if (! $record) {
                throw ValidationException::withMessages(['action' => 'يجب تسجيل الحضور أولًا.']);
            }
            if ($record->checked_out_at) {
                throw ValidationException::withMessages(['action' => 'تم تسجيل الانصراف اليوم بالفعل.']);
            }

            $record->update([
                'checked_out_at' => now(),
                'check_out_latitude' => $position['latitude'],
                'check_out_longitude' => $position['longitude'],
                'check_out_distance_meters' => round($distance, 2),
                'check_out_accuracy_meters' => $position['accuracy'],
            ]);

            return $record->fresh()->load('employee:id,first_name,last_name,employee_type');
        });
    }

    private function distanceInMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
