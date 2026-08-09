<?php

namespace App\Modules\Booking\Application\Services;

use App\Modules\Booking\Domain\Enums\InvoiceStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Booking\Infrastructure\Persistence\Models\Invoice\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoicePaymentService
{
    public function immediateRows(array $payments, int $totalCents, ?Booking $booking): array
    {
        $paymentRows = $this->normalizedRows($payments);
        if ($this->totalCents($paymentRows) !== $totalCents) {
            throw ValidationException::withMessages([
                'payments' => 'يجب أن يساوي مجموع مبالغ الدفع إجمالي الفاتورة بالضبط.',
            ]);
        }

        $this->assertImmediateAmounts($paymentRows, $totalCents);
        $this->assertDepositCovered($booking, $paymentRows, $totalCents);

        return $this->monetaryRows($paymentRows);
    }

    public function deferredRows(?Booking $booking, int $totalCents): array
    {
        if ($booking?->deposit === null) {
            return [];
        }

        $depositCents = (int) round((float) $booking->deposit->amount * 100);
        if ($depositCents > $totalCents) {
            throw ValidationException::withMessages([
                'discount' => 'لا يمكن أن يصبح إجمالي الفاتورة أقل من الديبوزيت المحصل.',
            ]);
        }

        return [[
            'payment_method' => $booking->deposit->payment_method,
            'amount' => $depositCents / 100,
        ]];
    }

    public function collect(Invoice $invoice, array $payments): Invoice
    {
        return DB::transaction(function () use ($invoice, $payments) {
            $lockedInvoice = Invoice::query()->lockForUpdate()->findOrFail($invoice->id);
            $this->assertCollectible($lockedInvoice);
            $paymentRows = $this->normalizedRows($payments);
            $this->assertPositiveAmounts($paymentRows);

            $remainingCents = $this->remainingCents($lockedInvoice);
            $collectedCents = $this->totalCents($paymentRows);
            $this->assertWithinRemainingBalance($collectedCents, $remainingCents);

            $lockedInvoice->payments()->createMany($this->monetaryRows($paymentRows));
            $this->updateSettlementStatus($lockedInvoice, $remainingCents - $collectedCents);

            return $lockedInvoice->load($this->invoiceRelations());
        });
    }

    public function statusFor(int $totalCents, array $paymentRows): InvoiceStatusEnum
    {
        $paidCents = (int) round(collect($paymentRows)->sum('amount') * 100);
        if ($totalCents === 0 || $paidCents === $totalCents) {
            return InvoiceStatusEnum::PAID;
        }

        return $paidCents > 0 ? InvoiceStatusEnum::PARTIALLY_PAID : InvoiceStatusEnum::UNPAID;
    }

    private function normalizedRows(array $payments): array
    {
        return array_map(fn (array $payment) => [
            'payment_method' => $payment['payment_method'],
            'amount_cents' => (int) round((float) $payment['amount'] * 100),
        ], $payments);
    }

    private function assertImmediateAmounts(array $paymentRows, int $totalCents): void
    {
        if ($totalCents > 0) {
            $this->assertPositiveAmounts($paymentRows);

            return;
        }

        if (count($paymentRows) !== 1 || $paymentRows[0]['amount_cents'] !== 0) {
            throw ValidationException::withMessages([
                'payments' => 'الفاتورة المجانية تقبل طريقة دفع واحدة فقط بمبلغ صفر.',
            ]);
        }
    }

    private function assertPositiveAmounts(array $paymentRows): void
    {
        if (collect($paymentRows)->contains(fn (array $payment) => $payment['amount_cents'] <= 0)) {
            throw ValidationException::withMessages([
                'payments' => 'يجب أن يكون مبلغ كل طريقة دفع أكبر من صفر.',
            ]);
        }
    }

    private function assertWithinRemainingBalance(int $collectedCents, int $remainingCents): void
    {
        if ($collectedCents > $remainingCents) {
            throw ValidationException::withMessages([
                'payments' => 'المبلغ المحصل أكبر من المبلغ المتبقي على الفاتورة.',
            ]);
        }
    }

    private function assertDepositCovered(?Booking $booking, array $paymentRows, int $totalCents): void
    {
        if ($booking?->deposit === null) {
            return;
        }

        $depositCents = (int) round((float) $booking->deposit->amount * 100);
        $methodCents = collect($paymentRows)
            ->where('payment_method', $booking->deposit->payment_method)
            ->sum('amount_cents');
        if ($depositCents > $totalCents || $methodCents < $depositCents) {
            throw ValidationException::withMessages([
                'payments' => 'يجب احتساب الديبوزيت المحصل ضمن مدفوعات الفاتورة.',
            ]);
        }
    }

    private function assertCollectible(Invoice $invoice): void
    {
        if (! in_array($invoice->status, [InvoiceStatusEnum::UNPAID->value, InvoiceStatusEnum::PARTIALLY_PAID->value], true)) {
            throw ValidationException::withMessages([
                'invoice_id' => 'لا يمكن تحصيل دفعة على فاتورة مسددة أو ملغاة.',
            ]);
        }
    }

    private function remainingCents(Invoice $invoice): int
    {
        $totalCents = (int) round((float) $invoice->total * 100);
        $paidCents = (int) round((float) $invoice->payments()->sum('amount') * 100);

        return $totalCents - $paidCents;
    }

    private function updateSettlementStatus(Invoice $invoice, int $remainingCents): void
    {
        $isPaid = $remainingCents === 0;
        $invoice->update([
            'status' => $isPaid ? InvoiceStatusEnum::PAID->value : InvoiceStatusEnum::PARTIALLY_PAID->value,
            'paid_at' => $isPaid ? now() : null,
        ]);
    }

    private function totalCents(array $paymentRows): int
    {
        return array_sum(array_column($paymentRows, 'amount_cents'));
    }

    private function monetaryRows(array $paymentRows): array
    {
        return array_map(fn (array $payment) => [
            'payment_method' => $payment['payment_method'],
            'amount' => $payment['amount_cents'] / 100,
        ], $paymentRows);
    }

    private function invoiceRelations(): array
    {
        return [
            'items',
            'payments',
            'salonCustomer',
            'booking:id,date,from_hour,to_hour',
            'cashier:id,first_name,last_name',
            'barber:id,first_name,last_name',
        ];
    }
}
