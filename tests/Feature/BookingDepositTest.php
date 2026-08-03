<?php

namespace Tests\Feature;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Application\DTOS\Booking\BookingDTO;
use App\Modules\Booking\Application\Services\BookingDepositService;
use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingDepositTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_booking_is_confirmed_with_deposit_and_arabic_service_snapshot(): void
    {
        Employee::query()->create([
            'first_name' => 'مدير',
            'employee_type' => 'super_admin',
            'status' => 'active',
        ]);
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
            [
                'service_id' => $service->id,
                'locale' => 'ar',
                'name' => 'حلاقة شعر',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => $service->id,
                'locale' => 'en',
                'name' => 'Haircut',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->withoutMiddleware()->postJson('/dashboard/create_booking', [
            'name' => 'عميل اختبار',
            'phone' => '01000000000',
            'date' => now()->addDays(2)->format('Y-m-d'),
            'from_hour' => '13:00',
            'employee_id' => $barber->id,
            'services' => [$service->id],
            'packages' => [],
            'deposit_amount' => 50,
            'deposit_payment_method' => 'cash',
        ]);

        $response->assertSuccessful();
        $booking = Booking::query()->latest('id')->firstOrFail();
        $this->assertSame(BookingStatusEnum::CONFIRMED->value, (int) $booking->status);
        $this->assertDatabaseHas('booking_deposits', [
            'booking_id' => $booking->id,
            'payment_method' => 'cash',
            'amount' => 50,
        ]);
        $this->assertDatabaseHas('booking_service', [
            'booking_id' => $booking->id,
            'service_id' => $service->id,
            'name_snapshot' => 'حلاقة شعر',
        ]);
    }

    public function test_valid_deposit_is_recorded_on_the_booking(): void
    {
        $booking = Booking::query()->create(['name' => 'عميل اختبار', 'status' => 1]);
        $bookingDTO = BookingDTO::fromArray([
            'deposit_amount' => 150,
            'deposit_payment_method' => 'wallet',
        ]);

        app(BookingDepositService::class)->collect($booking, $bookingDTO, 30000);

        $this->assertDatabaseHas('booking_deposits', [
            'booking_id' => $booking->id,
            'payment_method' => 'wallet',
            'amount' => 150,
        ]);
    }

    public function test_deposit_above_booking_total_is_rejected(): void
    {
        $booking = Booking::query()->create(['name' => 'عميل اختبار', 'status' => 1]);
        $bookingDTO = BookingDTO::fromArray([
            'deposit_amount' => 301,
            'deposit_payment_method' => 'cash',
        ]);

        $this->expectException(ValidationException::class);

        app(BookingDepositService::class)->collect($booking, $bookingDTO, 30000);
    }
}
