<?php

namespace Tests\Feature;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Service\Infrastructure\Persistence\Models\Package\Package;
use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicBookingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_public_barber_catalog_excludes_cashier_accounts(): void
    {
        $barber = Employee::query()->create([
            'first_name' => 'مصفف',
            'employee_type' => 'barber',
            'status' => 'active',
        ]);
        Employee::query()->create([
            'first_name' => 'كاشير',
            'employee_type' => 'employee',
            'status' => 'active',
        ]);

        $barberIds = $this->getJson('/api/barbers')
            ->assertSuccessful()
            ->json('data.*.id');

        $this->assertSame([$barber->id], $barberIds);
    }

    public function test_public_booking_is_confirmed_without_expiration(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-10 12:00', 'Africa/Cairo'));
        [$barber, $service] = $this->bookableSelection();

        $this->postJson('/api/create_booking', $this->bookingPayload($barber, $service, '13:00'))
            ->assertSuccessful();

        $booking = Booking::query()->sole();
        $this->assertSame(BookingStatusEnum::CONFIRMED->value, (int) $booking->status);
        $this->assertNull($booking->expires_at);
    }

    public function test_active_package_remains_bookable_when_a_service_is_hidden_from_individual_booking(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-10 12:00', 'Africa/Cairo'));
        [$barber, $service] = $this->bookableSelection();
        $service->update(['is_active' => false]);
        $package = Package::query()->create(['price' => 300, 'is_active' => true]);
        $package->services()->attach($service->id, ['quantity' => 1, 'sort_order' => 0]);
        $package->translateOrNew('ar')->name = 'باقة العريس';
        $package->translateOrNew('en')->name = 'Groom package';
        $package->save();

        $this->postJson('/api/fetch_packages')
            ->assertSuccessful()
            ->assertJsonPath('data.0.id', $package->id)
            ->assertJsonPath('data.0.translations.ar.name', 'باقة العريس');

        $payload = $this->bookingPayload($barber, $service, '13:00');
        $payload['services'] = [];
        $payload['packages'] = [$package->id];

        $this->postJson('/api/create_booking', $payload)->assertSuccessful();
        $this->assertDatabaseHas('booking_package', ['package_id' => $package->id]);
    }

    public function test_midnight_booking_occupies_the_next_day_slot_of_the_selected_business_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-10 12:00', 'Africa/Cairo'));
        [$barber, $service] = $this->bookableSelection();
        $slotPayload = [
            'employee_id' => $barber->id,
            'date' => '2026-08-10',
            'services' => [$service->id],
            'packages' => [],
        ];

        $slots = $this->postJson('/api/available_slots', $slotPayload)
            ->assertSuccessful()
            ->json('data.slots');
        $midnightSlot = collect($slots)->firstWhere('from_time', '00:00');
        $this->assertNotNull($midnightSlot);
        $this->assertSame('2026-08-11', $midnightSlot['from_date']);

        $this->postJson('/api/create_booking', $this->bookingPayload($barber, $service, '00:00'))
            ->assertSuccessful();

        $remainingSlots = $this->postJson('/api/available_slots', $slotPayload)
            ->assertSuccessful()
            ->json('data.slots');
        $this->assertNotContains('00:00', array_column($remainingSlots, 'from_time'));
    }

    private function bookableSelection(): array
    {
        $barber = Employee::query()->create([
            'first_name' => 'مصفف',
            'employee_type' => 'barber',
            'status' => 'active',
        ]);
        $service = Service::query()->create([
            'price' => 170,
            'duration' => 30,
            'is_active' => true,
        ]);
        DB::table('service_translations')->insert([
            'service_id' => $service->id,
            'locale' => 'ar',
            'name' => 'حلاقة شعر',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$barber, $service];
    }

    private function bookingPayload(Employee $barber, Service $service, string $fromHour): array
    {
        return [
            'name' => 'عميل الموقع',
            'phone' => '01000000000',
            'date' => '2026-08-10',
            'from_hour' => $fromHour,
            'employee_id' => $barber->id,
            'services' => [$service->id],
            'packages' => [],
        ];
    }
}
