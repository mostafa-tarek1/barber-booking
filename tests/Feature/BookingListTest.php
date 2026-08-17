<?php

namespace Tests\Feature;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingListTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

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

    public function test_booking_list_switches_between_nearest_appointments_and_latest_created(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-15 12:00', 'Africa/Cairo'));
        $superAdmin = $this->employee('مدير', 'super_admin');
        $barber = $this->employee('مصطفى', 'barber');
        $service = Service::query()->create(['price' => 200, 'duration' => 30, 'is_active' => true]);
        $nearest = $this->bookingFor($service, $barber, ['name' => 'الأقرب', 'date' => '2026-08-16', 'created_at' => '2026-08-10 10:00:00']);
        $latest = $this->bookingFor($service, $barber, ['name' => 'الأحدث', 'date' => '2026-08-18', 'created_at' => '2026-08-15 11:00:00']);
        $past = $this->bookingFor($service, $barber, ['name' => 'السابق', 'date' => '2026-08-14', 'created_at' => '2026-08-12 10:00:00']);

        $nearestIds = $this->actingAs($superAdmin, 'employee')
            ->postJson('/dashboard/fetch_bookings', ['paginate' => 1, 'sort' => 'appointment'])
            ->assertSuccessful()
            ->json('data.data.*.id');
        $latestIds = $this->postJson('/dashboard/fetch_bookings', ['paginate' => 1, 'sort' => 'latest'])
            ->assertSuccessful()
            ->json('data.data.*.id');

        $this->assertSame([$nearest->id, $latest->id, $past->id], $nearestIds);
        $this->assertSame([$latest->id, $past->id, $nearest->id], $latestIds);
    }

    public function test_booking_list_filters_by_assigned_barber(): void
    {
        $superAdmin = $this->employee('مدير', 'super_admin');
        $selectedBarber = $this->employee('مصطفى', 'barber');
        $otherBarber = $this->employee('محمد', 'barber');
        $service = Service::query()->create(['price' => 200, 'duration' => 30, 'is_active' => true]);
        $selectedBooking = $this->bookingFor($service, $selectedBarber, ['name' => 'عميل مصطفى', 'date' => '2026-08-16']);
        $this->bookingFor($service, $otherBarber, ['name' => 'عميل محمد', 'date' => '2026-08-16']);

        $response = $this->actingAs($superAdmin, 'employee')
            ->postJson('/dashboard/fetch_bookings', [
                'paginate' => 1,
                'employee_id' => $selectedBarber->id,
            ])
            ->assertSuccessful();

        $this->assertSame([$selectedBooking->id], $response->json('data.data.*.id'));
    }

    public function test_default_booking_list_hides_cancelled_and_finished_bookings_until_their_status_is_requested(): void
    {
        $superAdmin = $this->employee('مدير', 'super_admin');
        $barber = $this->employee('مصطفى', 'barber');
        $service = Service::query()->create(['price' => 200, 'duration' => 30, 'is_active' => true]);
        $activeBooking = $this->bookingFor($service, $barber, ['name' => 'حجز نشط', 'status' => 1]);
        $cancelledBooking = $this->bookingFor($service, $barber, ['name' => 'حجز ملغي', 'status' => 2]);
        $finishedBooking = $this->bookingFor($service, $barber, ['name' => 'حجز مكتمل', 'status' => 3]);

        $defaultBookingIds = $this->actingAs($superAdmin, 'employee')
            ->postJson('/dashboard/fetch_bookings', ['paginate' => 1])
            ->assertSuccessful()
            ->json('data.data.*.id');
        $finishedBookingIds = $this->postJson('/dashboard/fetch_bookings', [
            'paginate' => 1,
            'status' => 3,
        ])
            ->assertSuccessful()
            ->json('data.data.*.id');
        $cancelledBookingIds = $this->postJson('/dashboard/fetch_bookings', [
            'paginate' => 1,
            'status' => 2,
        ])
            ->assertSuccessful()
            ->json('data.data.*.id');

        $this->assertSame([$activeBooking->id], $defaultBookingIds);
        $this->assertSame([$finishedBooking->id], $finishedBookingIds);
        $this->assertSame([$cancelledBooking->id], $cancelledBookingIds);
    }

    private function bookingFor(Service $service, Employee $barber, array $bookingAttributes): Booking
    {
        $createdAt = $bookingAttributes['created_at'] ?? null;
        unset($bookingAttributes['created_at']);
        $booking = Booking::query()->create(array_merge([
            'from_hour' => '14:00',
            'to_hour' => '14:30',
            'status' => 1,
        ], $bookingAttributes));
        if ($createdAt) {
            $booking->forceFill(['created_at' => $createdAt])->saveQuietly();
        }
        $booking->services()->attach($service->id, ['employee_id' => $barber->id]);

        return $booking;
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
