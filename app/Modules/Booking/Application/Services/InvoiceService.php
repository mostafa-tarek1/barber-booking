<?php

namespace App\Modules\Booking\Application\Services;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use App\Modules\Booking\Domain\Enums\InvoiceStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Booking\Infrastructure\Persistence\Models\Invoice\Invoice;
use App\Modules\Booking\Infrastructure\Persistence\Models\SalonCustomer\SalonCustomer;
use App\Modules\Service\Infrastructure\Persistence\Models\Package\Package;
use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function __construct(private InvoicePaymentService $invoicePaymentService) {}

    public function create(array $invoiceInput, int $cashierId): Invoice
    {
        return DB::transaction(function () use ($invoiceInput, $cashierId) {
            $booking = $this->lockedBooking($invoiceInput['booking_id'] ?? null);
            $salonCustomer = $this->salonCustomer($booking, $invoiceInput);
            $invoiceInput['salon_customer_id'] = $salonCustomer?->id;
            $barber = $this->resolveBarber($booking, $invoiceInput['barber_id'] ?? null);
            $requestedItems = $this->removeAlreadyBookedItems($booking, $invoiceInput['items']);
            $items = [
                ...$this->bookedItems($booking),
                ...$this->pricedItems($requestedItems),
            ];
            if ($items === []) {
                throw ValidationException::withMessages(['items' => 'يجب إضافة بند واحد على الأقل للفاتورة.']);
            }
            $totals = $this->invoiceTotals($items, (float) ($invoiceInput['discount'] ?? 0));
            $paymentRows = $this->initialPaymentRows($invoiceInput, $totals['total_cents'], $booking);
            $status = $this->invoicePaymentService->statusFor($totals['total_cents'], $paymentRows);
            $invoice = Invoice::create($this->invoiceAttributes(
                $invoiceInput,
                $booking,
                $barber->id,
                $cashierId,
                $totals,
                $paymentRows,
                $status
            ));

            $invoice->update(['invoice_number' => 'KR-'.now()->format('Ymd').'-'.str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT)]);
            $invoice->items()->createMany($this->invoiceItemRows($items));
            $invoice->payments()->createMany($paymentRows);
            $booking?->update(['status' => BookingStatusEnum::FINISHED->value, 'updated_by' => $cashierId]);

            return $invoice->load([
                'items',
                'payments',
                'salonCustomer',
                'booking:id,date,from_hour,to_hour',
                'cashier:id,first_name,last_name',
                'barber:id,first_name,last_name',
            ]);
        });
    }

    private function invoiceTotals(array $items, float $discount): array
    {
        $subtotalCents = collect($items)->sum('total_cents');
        $discountCents = (int) round($discount * 100);
        if ($discountCents > $subtotalCents) {
            throw ValidationException::withMessages(['discount' => 'Discount cannot exceed subtotal.']);
        }

        $totalCents = $subtotalCents - $discountCents;

        return [
            'subtotal' => $subtotalCents / 100,
            'discount' => $discountCents / 100,
            'total' => $totalCents / 100,
            'total_cents' => $totalCents,
        ];
    }

    private function invoiceAttributes(
        array $invoiceInput,
        ?Booking $booking,
        int $barberId,
        int $cashierId,
        array $totals,
        array $paymentRows,
        InvoiceStatusEnum $status
    ): array {
        return [
            'booking_id' => $booking?->id,
            'salon_customer_id' => $invoiceInput['salon_customer_id'],
            'customer_name' => $booking?->name ?? $invoiceInput['customer_name'],
            'customer_phone' => $booking?->phone ?? ($invoiceInput['customer_phone'] ?? null),
            'cashier_id' => $cashierId,
            'barber_id' => $barberId,
            'subtotal' => $totals['subtotal'],
            'discount' => $totals['discount'],
            'total' => $totals['total'],
            'payment_method' => $paymentRows[0]['payment_method'] ?? 'cash',
            'status' => $status->value,
            'notes' => $invoiceInput['notes'] ?? null,
            'paid_at' => $status === InvoiceStatusEnum::PAID ? now() : null,
        ];
    }

    private function salonCustomer(?Booking $booking, array $invoiceInput): ?SalonCustomer
    {
        $name = trim($booking?->name ?? $invoiceInput['customer_name']);
        $phone = trim($booking?->phone ?? ($invoiceInput['customer_phone'] ?? ''));
        $normalizedPhone = PhoneNumber::normalize($phone);
        if (! $normalizedPhone) {
            return null;
        }

        SalonCustomer::query()->upsert([[
            'name' => $name,
            'phone' => $phone,
            'normalized_phone' => $normalizedPhone,
            'created_at' => now(),
            'updated_at' => now(),
        ]], ['normalized_phone'], ['name', 'phone', 'updated_at']);

        return SalonCustomer::query()->where('normalized_phone', $normalizedPhone)->firstOrFail();
    }

    private function initialPaymentRows(array $invoiceInput, int $totalCents, ?Booking $booking): array
    {
        if (($invoiceInput['settlement_type'] ?? 'immediate') === 'deferred') {
            return $this->invoicePaymentService->deferredRows($booking, $totalCents);
        }

        return $this->invoicePaymentService->immediateRows(
            $invoiceInput['payments'],
            $totalCents,
            $booking
        );
    }

    private function resolveBarber(?Booking $booking, ?int $requestedBarberId): Employee
    {
        $bookingBarberId = $booking?->services->pluck('pivot.employee_id')->filter()->first();
        if ($bookingBarberId && $requestedBarberId && (int) $bookingBarberId !== $requestedBarberId) {
            throw ValidationException::withMessages(['barber_id' => 'يجب استخدام الحلاق المسجل على الحجز.']);
        }

        $barberId = $bookingBarberId ?: $requestedBarberId;
        if (! $barberId) {
            throw ValidationException::withMessages(['barber_id' => 'اختيار الحلاق مطلوب لإصدار الفاتورة.']);
        }

        $barber = Employee::query()->find($barberId);
        if (! $barber || $barber->employee_type !== 'barber') {
            throw ValidationException::withMessages(['barber_id' => 'الحلاق المحدد غير متاح.']);
        }
        if (! $booking && $barber->status !== 'active') {
            throw ValidationException::withMessages(['barber_id' => 'لا يمكن إسناد عميل مباشر لحلاق معطل.']);
        }

        return $barber;
    }

    private function lockedBooking(?int $bookingId): ?Booking
    {
        if (! $bookingId) {
            return null;
        }

        $booking = Booking::query()
            ->with(['services.translations', 'packages.translations', 'deposit'])
            ->lockForUpdate()
            ->findOrFail($bookingId);
        if ($booking->status === BookingStatusEnum::CANCELLED->value) {
            throw ValidationException::withMessages(['booking_id' => 'لا يمكن إصدار فاتورة لحجز ملغي.']);
        }
        if ($booking->status === BookingStatusEnum::PENDING->value
            && $booking->expires_at?->isPast()) {
            $booking->update(['status' => BookingStatusEnum::CANCELLED->value, 'expires_at' => null]);
            throw ValidationException::withMessages(['booking_id' => 'انتهت مهلة هذا الحجز.']);
        }
        if (Invoice::query()->where('booking_id', $bookingId)->exists()) {
            throw ValidationException::withMessages(['booking_id' => 'تم إصدار فاتورة لهذا الحجز من قبل.']);
        }

        return $booking;
    }

    private function removeAlreadyBookedItems(?Booking $booking, array $requestedItems): array
    {
        if (! $booking) {
            return $requestedItems;
        }

        $remainingBookedServices = $booking->services
            ->filter(fn (Service $service) => ! $service->pivot->package_id)
            ->countBy(fn (Service $service) => (int) $service->id)
            ->all();
        $remainingBookedPackages = $booking->packages
            ->countBy(fn (Package $package) => (int) $package->id)
            ->all();

        return collect($requestedItems)->filter(function (array $item) use (
            &$remainingBookedServices,
            &$remainingBookedPackages
        ) {
            $serviceId = isset($item['service_id']) ? (int) $item['service_id'] : null;
            if ($serviceId && ($remainingBookedServices[$serviceId] ?? 0) > 0) {
                $remainingBookedServices[$serviceId]--;

                return false;
            }

            $packageId = isset($item['package_id']) ? (int) $item['package_id'] : null;
            if ($packageId && ($remainingBookedPackages[$packageId] ?? 0) > 0) {
                $remainingBookedPackages[$packageId]--;

                return false;
            }

            return true;
        })->values()->all();
    }

    private function bookedItems(?Booking $booking): array
    {
        if (! $booking) {
            return [];
        }

        $packageItems = $booking->packages->map(fn (Package $package) => [
            'service_id' => null,
            'package_id' => $package->id,
            'employee_id' => $booking->services->first()?->pivot->employee_id,
            'description' => $package->translate('ar')?->name
                ?: ($package->pivot->name_snapshot ?: $package->name),
            'quantity' => 1,
            'unit_price_cents' => (int) round((float) $package->pivot->price_snapshot * 100),
            'total_cents' => (int) round((float) $package->pivot->price_snapshot * 100),
        ]);

        $directServiceItems = $booking->services
            ->filter(fn (Service $service) => ! $service->pivot->package_id)
            ->map(function (Service $service) {
                $quantity = (int) ($service->pivot->quantity ?: 1);
                $unitPriceCents = (int) round(
                    (float) ($service->pivot->price_snapshot ?? $service->price) * 100
                );

                return [
                    'service_id' => $service->id,
                    'package_id' => null,
                    'employee_id' => $service->pivot->employee_id,
                    'description' => $service->translate('ar')?->name
                        ?: ($service->pivot->name_snapshot ?: $service->name),
                    'quantity' => $quantity,
                    'unit_price_cents' => $unitPriceCents,
                    'total_cents' => $unitPriceCents * $quantity,
                ];
            });

        return $packageItems->concat($directServiceItems)->values()->all();
    }

    private function pricedItems(array $requestedItems): array
    {
        $serviceIds = collect($requestedItems)->pluck('service_id')->filter()->unique();
        $services = Service::query()->whereIn('id', $serviceIds)->get()->keyBy('id');
        $packageIds = collect($requestedItems)->pluck('package_id')->filter()->unique();
        $packages = Package::query()
            ->whereIn('id', $packageIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        return collect($requestedItems)->map(function (array $requestedItem) use ($services, $packages) {
            $serviceId = $requestedItem['service_id'] ?? null;
            $service = $serviceId ? $services->get($serviceId) : null;
            $packageId = $requestedItem['package_id'] ?? null;
            $package = $packageId ? $packages->get($packageId) : null;
            if ($serviceId && ! $service) {
                throw ValidationException::withMessages(['items' => 'One or more services do not exist.']);
            }
            if ($packageId && ! $package) {
                throw ValidationException::withMessages(['items' => 'إحدى الباكدجات غير متاحة.']);
            }
            if (! $service && ! $package
                && (! isset($requestedItem['description']) || ! array_key_exists('unit_price', $requestedItem))) {
                throw ValidationException::withMessages(['items' => 'Custom items require a description and price.']);
            }

            $quantity = (int) $requestedItem['quantity'];
            $unitPriceCents = (int) round(
                ((float) ($service?->price ?? $package?->price ?? $requestedItem['unit_price'])) * 100
            );

            return [
                'service_id' => $service?->id,
                'package_id' => $package?->id,
                'employee_id' => $requestedItem['employee_id'] ?? null,
                'description' => $service?->translate(app()->getLocale())?->name
                    ?? $service?->name
                    ?? $package?->translate(app()->getLocale())?->name
                    ?? $package?->name
                    ?? $requestedItem['description'],
                'quantity' => $quantity,
                'unit_price_cents' => $unitPriceCents,
                'total_cents' => $unitPriceCents * $quantity,
            ];
        })->all();
    }

    private function invoiceItemRows(array $pricedItems): array
    {
        return collect($pricedItems)->map(fn (array $item) => [
            'service_id' => $item['service_id'],
            'package_id' => $item['package_id'],
            'employee_id' => $item['employee_id'],
            'description' => $item['description'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price_cents'] / 100,
            'total' => $item['total_cents'] / 100,
        ])->all();
    }
}
