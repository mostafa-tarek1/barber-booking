<?php

namespace App\Modules\Booking\Application\Services;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Booking\Infrastructure\Persistence\Models\Invoice\Invoice;
use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function create(array $invoiceInput, int $cashierId): Invoice
    {
        return DB::transaction(function () use ($invoiceInput, $cashierId) {
            $booking = $this->lockedBooking($invoiceInput['booking_id'] ?? null);
            $barber = $this->resolveBarber($booking, $invoiceInput['barber_id'] ?? null);
            $this->validateBookingServices($booking, $invoiceInput['items']);
            $items = $this->pricedItems($invoiceInput['items']);
            $totals = $this->invoiceTotals($items, (float) ($invoiceInput['discount'] ?? 0));
            $invoice = Invoice::create($this->invoiceAttributes($invoiceInput, $booking, $barber->id, $cashierId, $totals));

            $invoice->update(['invoice_number' => 'KR-'.now()->format('Ymd').'-'.str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT)]);
            $invoice->items()->createMany($this->invoiceItemRows($items));
            $booking?->update(['status' => BookingStatusEnum::FINISHED->value, 'updated_by' => $cashierId]);

            return $invoice->load([
                'items',
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

        return [
            'subtotal' => $subtotalCents / 100,
            'discount' => $discountCents / 100,
            'total' => ($subtotalCents - $discountCents) / 100,
        ];
    }

    private function invoiceAttributes(
        array $invoiceInput,
        ?Booking $booking,
        int $barberId,
        int $cashierId,
        array $totals
    ): array {
        return [
            'booking_id' => $booking?->id,
            'customer_name' => $booking?->name ?? $invoiceInput['customer_name'],
            'customer_phone' => $booking?->phone ?? ($invoiceInput['customer_phone'] ?? null),
            'cashier_id' => $cashierId,
            'barber_id' => $barberId,
            ...$totals,
            'payment_method' => $invoiceInput['payment_method'],
            'notes' => $invoiceInput['notes'] ?? null,
            'paid_at' => now(),
        ];
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

        $booking = Booking::query()->with('services')->lockForUpdate()->findOrFail($bookingId);
        if ($booking->status === BookingStatusEnum::CANCELLED->value) {
            throw ValidationException::withMessages(['booking_id' => 'A cancelled booking cannot be invoiced.']);
        }
        if (Invoice::query()->where('booking_id', $bookingId)->exists()) {
            throw ValidationException::withMessages(['booking_id' => 'This booking already has an invoice.']);
        }

        return $booking;
    }

    private function validateBookingServices(?Booking $booking, array $requestedItems): void
    {
        if (! $booking) {
            return;
        }

        $requestedServiceIds = collect($requestedItems)->pluck('service_id')->filter();
        $missingServiceIds = $booking->services->pluck('id')->diff($requestedServiceIds);
        if ($missingServiceIds->isNotEmpty()) {
            throw ValidationException::withMessages(['items' => 'Booked services cannot be removed from the invoice.']);
        }
    }

    private function pricedItems(array $requestedItems): array
    {
        $serviceIds = collect($requestedItems)->pluck('service_id')->filter()->unique();
        $services = Service::query()->whereIn('id', $serviceIds)->get()->keyBy('id');

        return collect($requestedItems)->map(function (array $requestedItem) use ($services) {
            $serviceId = $requestedItem['service_id'] ?? null;
            $service = $serviceId ? $services->get($serviceId) : null;
            if ($serviceId && ! $service) {
                throw ValidationException::withMessages(['items' => 'One or more services do not exist.']);
            }
            if (! $service && (! isset($requestedItem['description']) || ! array_key_exists('unit_price', $requestedItem))) {
                throw ValidationException::withMessages(['items' => 'Custom items require a description and price.']);
            }

            $quantity = (int) $requestedItem['quantity'];
            $unitPriceCents = (int) round(((float) ($service?->price ?? $requestedItem['unit_price'])) * 100);

            return [
                'service_id' => $service?->id,
                'employee_id' => $requestedItem['employee_id'] ?? null,
                'description' => $service?->translate(app()->getLocale())?->name
                    ?? $service?->name
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
            'employee_id' => $item['employee_id'],
            'description' => $item['description'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price_cents'] / 100,
            'total' => $item['total_cents'] / 100,
        ])->all();
    }
}
