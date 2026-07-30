<?php

namespace App\Modules\Booking\Http\Controllers\Dashboard\Invoice;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Application\Services\InvoiceService;
use App\Modules\Booking\Infrastructure\Persistence\Models\Invoice\Invoice;
use App\Support\BusinessDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function create(Request $request, InvoiceService $invoiceService)
    {
        $validated = $request->validate([
            'booking_id' => ['nullable', 'integer', 'exists:bookings,id'],
            'barber_id' => ['nullable', 'integer', 'exists:employees,id'],
            'customer_name' => ['required_without:booking_id', 'nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.service_id' => ['nullable', 'integer', 'exists:services,id'],
            'items.*.package_id' => ['nullable', 'integer', 'exists:packages,id'],
            'items.*.employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'items.*.description' => ['required_without_all:items.*.service_id,items.*.package_id', 'nullable', 'string', 'max:255'],
            'items.*.unit_price' => ['required_without_all:items.*.service_id,items.*.package_id', 'nullable', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::in(['cash', 'card', 'wallet'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $invoice = $invoiceService->create($validated, $request->user()->id);

        return response()->json(['status' => 201, 'message' => 'Invoice created successfully', 'data' => $invoice], 201);
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'word' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $invoices = Invoice::query()
            ->with(['cashier:id,first_name,last_name', 'barber:id,first_name,last_name'])
            ->when(
                $request->user()->employee_type === 'barber',
                fn ($query) => $query->where('barber_id', $request->user()->id)
            )
            ->when($validated['word'] ?? null, function ($query, $word) {
                $query->where(fn ($search) => $search
                    ->where('invoice_number', 'like', "%$word%")
                    ->orWhere('customer_name', 'like', "%$word%")
                    ->orWhere('customer_phone', 'like', "%$word%"));
            })
            ->when($validated['date_from'] ?? null, function ($query, $date) {
                [$start] = BusinessDay::range($date);

                $query->where('paid_at', '>=', $start);
            })
            ->when($validated['date_to'] ?? null, function ($query, $date) {
                [, $end] = BusinessDay::range($date);

                $query->where('paid_at', '<', $end);
            })
            ->latest('paid_at')
            ->paginate($validated['limit'] ?? 20);

        return response()->json(['status' => 200, 'message' => 'Invoices fetched successfully', 'data' => $invoices]);
    }

    public function show(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => ['required', 'integer', 'exists:invoices,id'],
        ]);

        $invoice = Invoice::query()
            ->with([
                'items',
                'booking:id,date,from_hour,to_hour',
                'cashier:id,first_name,last_name',
                'barber:id,first_name,last_name',
            ])
            ->findOrFail($validated['invoice_id']);

        abort_if(
            $request->user()->employee_type === 'barber'
                && (int) $invoice->barber_id !== (int) $request->user()->id,
            403,
            'ليس لديك صلاحية لعرض هذه الفاتورة.'
        );

        return response()->json(['status' => 200, 'message' => 'Invoice fetched successfully', 'data' => $invoice]);
    }

    public function barberSalesReport(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'barber_id' => ['nullable', 'integer', 'exists:employees,id'],
        ]);
        if ($request->user()->employee_type === 'barber') {
            $validated['barber_id'] = $request->user()->id;
        }

        $report = Employee::query()
            ->where('employee_type', 'barber')
            ->when($validated['barber_id'] ?? null, fn ($query, $barberId) => $query->where('employees.id', $barberId))
            ->leftJoin('invoices', function ($join) use ($validated) {
                $join->on('invoices.barber_id', '=', 'employees.id')
                    ->where('invoices.status', '=', 'paid');
                if ($validated['date_from'] ?? null) {
                    [$start] = BusinessDay::range($validated['date_from']);
                    $join->where('invoices.paid_at', '>=', $start);
                }
                if ($validated['date_to'] ?? null) {
                    [, $end] = BusinessDay::range($validated['date_to']);
                    $join->where('invoices.paid_at', '<', $end);
                }
            })
            ->select([
                'employees.id',
                'employees.first_name',
                'employees.last_name',
                'employees.status',
                DB::raw('COUNT(invoices.id) as clients_count'),
                DB::raw('COALESCE(SUM(invoices.total), 0) as total_sales'),
                DB::raw('SUM(CASE WHEN invoices.booking_id IS NOT NULL THEN 1 ELSE 0 END) as booking_clients'),
                DB::raw('SUM(CASE WHEN invoices.id IS NOT NULL AND invoices.booking_id IS NULL THEN 1 ELSE 0 END) as walk_in_clients'),
            ])
            ->groupBy('employees.id', 'employees.first_name', 'employees.last_name', 'employees.status')
            ->orderByDesc('total_sales')
            ->get();

        return response()->json(['status' => 200, 'message' => 'Barber sales report fetched successfully', 'data' => $report]);
    }
}
