<?php

namespace Tests\Feature;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Notification\Application\Jobs\SendWhatsAppMessage;
use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BookingWhatsAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_confirmed_public_booking_queues_admin_and_customer_messages(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-20 12:00', 'Africa/Cairo'));
        $this->configureBridge();
        Queue::fake();
        [$barber, $service] = $this->bookableSelection();

        $this->postJson('/api/create_booking', $this->bookingPayload($barber, $service, '14:00'))
            ->assertSuccessful();

        Queue::assertPushed(SendWhatsAppMessage::class, 2);
        Queue::assertPushed(fn (SendWhatsAppMessage $job) => $job->phone === '+201000000000'
            && str_contains($job->message, 'حجز جديد'));
        Queue::assertPushed(fn (SendWhatsAppMessage $job) => $job->phone === '01000000000'
            && str_contains($job->message, 'تم تأكيد حجزك'));
    }

    public function test_due_reminder_is_queued_only_once(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-20 13:00', 'Africa/Cairo'));
        $this->configureBridge();
        Queue::fake();
        [$barber, $service] = $this->bookableSelection();
        $booking = $this->confirmedBooking($barber, $service, '14:00');

        $this->artisan('bookings:send-whatsapp-reminders')->assertSuccessful();
        $this->artisan('bookings:send-whatsapp-reminders')->assertSuccessful();

        Queue::assertPushed(SendWhatsAppMessage::class, 1);
        Queue::assertPushed(fn (SendWhatsAppMessage $job) => $job->phone === $booking->phone
            && str_contains($job->message, 'اقترب موعد حجزك'));
        $this->assertNotNull($booking->fresh()->whatsapp_reminder_sent_at);
    }

    private function configureBridge(): void
    {
        config()->set([
            'whatsapp.url' => 'http://bridge.test',
            'whatsapp.api_key' => 'test-key',
            'whatsapp.session' => 'main',
            'whatsapp.admin_phone' => '+201000000000',
            'whatsapp.reminder_minutes' => 60,
        ]);
    }

    private function bookableSelection(): array
    {
        $barber = Employee::query()->create([
            'first_name' => 'مصطفى',
            'employee_type' => 'barber',
            'status' => 'active',
        ]);
        $service = Service::query()->create(['price' => 170, 'duration' => 30, 'is_active' => true]);
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
            'date' => '2026-08-20',
            'from_hour' => $fromHour,
            'employee_id' => $barber->id,
            'services' => [$service->id],
            'packages' => [],
        ];
    }

    private function confirmedBooking(Employee $barber, Service $service, string $fromHour): Booking
    {
        $booking = Booking::query()->create([
            'name' => 'عميل الموقع',
            'phone' => '01000000000',
            'date' => '2026-08-20',
            'from_hour' => $fromHour,
            'to_hour' => '14:30',
            'status' => BookingStatusEnum::CONFIRMED->value,
        ]);
        $booking->services()->attach($service->id, [
            'employee_id' => $barber->id,
            'name_snapshot' => 'حلاقة شعر',
            'duration_snapshot' => 30,
        ]);

        return $booking;
    }
}
