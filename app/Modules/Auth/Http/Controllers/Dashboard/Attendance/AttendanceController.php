<?php

namespace App\Modules\Auth\Http\Controllers\Dashboard\Attendance;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Application\Services\AttendanceService;
use App\Modules\Auth\Infrastructure\Persistence\Models\Attendance\AttendanceRecord;
use App\Modules\Auth\Infrastructure\Persistence\Models\Attendance\AttendanceSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function status(Request $request)
    {
        $employee = $request->user();
        $record = AttendanceRecord::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', now('Africa/Cairo')->toDateString())
            ->first();
        $setting = AttendanceSetting::query()->latest('id')->first();

        return response()->json([
            'status' => 200,
            'message' => 'Attendance status fetched successfully',
            'data' => [
                'record' => $record,
                'location_configured' => (bool) $setting,
                'allowed_radius_meters' => $setting?->allowed_radius_meters ?? 50,
                'can_manage' => in_array($employee->employee_type, ['admin', 'super_admin'], true),
            ],
        ]);
    }

    public function punch(Request $request, AttendanceService $attendanceService)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['check_in', 'check_out'])],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:10000'],
        ]);

        $record = $attendanceService->punch(
            $request->user(),
            $validated['action'],
            [
                'latitude' => (float) $validated['latitude'],
                'longitude' => (float) $validated['longitude'],
                'accuracy' => isset($validated['accuracy']) ? (float) $validated['accuracy'] : null,
            ]
        );

        return response()->json(['status' => 200, 'message' => 'تم تسجيل البصمة بنجاح', 'data' => $record]);
    }

    public function settings(Request $request)
    {
        $this->ensureAdmin($request);
        $setting = AttendanceSetting::query()->latest('id')->first();

        return response()->json(['status' => 200, 'message' => 'Attendance settings fetched successfully', 'data' => $setting]);
    }

    public function updateSettings(Request $request)
    {
        $this->ensureAdmin($request);
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $values = [
            ...$validated,
            'allowed_radius_meters' => 50,
            'updated_by' => $request->user()->id,
        ];
        $setting = AttendanceSetting::query()->first();
        if ($setting) {
            $setting->update($values);
        } else {
            $setting = AttendanceSetting::create($values);
        }

        return response()->json(['status' => 200, 'message' => 'تم حفظ موقع الفرع ونطاق 50 متر', 'data' => $setting->fresh()]);
    }

    public function report(Request $request)
    {
        $this->ensureAdmin($request);
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
        ]);

        $records = AttendanceRecord::query()
            ->with('employee:id,first_name,last_name,employee_type')
            ->when($validated['date_from'] ?? null, fn ($query, $date) => $query->whereDate('attendance_date', '>=', $date))
            ->when($validated['date_to'] ?? null, fn ($query, $date) => $query->whereDate('attendance_date', '<=', $date))
            ->when($validated['employee_id'] ?? null, fn ($query, $employeeId) => $query->where('employee_id', $employeeId))
            ->latest('attendance_date')
            ->latest('checked_in_at')
            ->limit(500)
            ->get();

        return response()->json(['status' => 200, 'message' => 'Attendance report fetched successfully', 'data' => $records]);
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless(
            in_array($request->user()->employee_type, ['admin', 'super_admin'], true),
            403,
            'هذه العملية متاحة للأدمن فقط.'
        );
    }
}
