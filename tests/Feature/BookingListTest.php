<?php

namespace Tests\Feature;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingListTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_booking_list_includes_the_assigned_barber(): void
    {
        $superAdmin = $this->employee('مدير', 'super_admin');
        $barber = $this->employee('مصطفى', 'barber', 'طارق');
        $service = Service::query()->create(['price' => 225, 'duration' => 45, 'is_active' => true]);
        $booking = Booking::query()->create([
            'name' => 'عميل اختبار',
            'date' => '2026-08-13',
            'from_hour' => '20:00',
            'to_hour' => '20:45',
            'status' => 1,
        ]);
        $booking->services()->attach($service->id, [
            'employee_id' => $barber->id,
            'name_snapshot' => 'حلاقة شعر',
            'price_snapshot' => 225,
            'duration_snapshot' => 45,
        ]);

        $this->actingAs($superAdmin, 'employee')
            ->postJson('/dashboard/fetch_bookings', ['paginate' => 1])
            ->assertSuccessful()
            ->assertJsonPath('data.data.0.employee.id', $barber->id)
            ->assertJsonPath('data.data.0.employee.name', 'مصطفى طارق');
    }

    private function employee(string $firstName, string $role, ?string $lastName = null): Employee
    {
        return Employee::query()->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'employee_type' => $role,
            'status' => 'active',
        ]);
    }
}
