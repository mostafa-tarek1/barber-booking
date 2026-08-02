<?php

namespace App\Modules\Booking\Http\Controllers\Dashboard\SalonCustomer;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Infrastructure\Persistence\Models\SalonCustomer\SalonCustomer;
use App\Support\PhoneNumber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SalonCustomerController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'word' => ['nullable', 'string', 'max:255'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $customers = $this->customerQuery($validated['word'] ?? null)
            ->orderByDesc('last_visit_at')
            ->paginate($validated['limit'] ?? 20);

        return response()->json([
            'status' => 200,
            'message' => 'Salon customers fetched successfully',
            'data' => $customers,
        ]);
    }

    private function customerQuery(?string $word): Builder
    {
        $normalizedPhone = PhoneNumber::normalize($word);

        return SalonCustomer::query()
            ->withCount(['invoices as visits_count' => fn ($query) => $query->where('status', 'paid')])
            ->withSum(['invoices as total_spent' => fn ($query) => $query->where('status', 'paid')], 'total')
            ->withMax(['invoices as last_visit_at' => fn ($query) => $query->where('status', 'paid')], 'paid_at')
            ->when($word, fn ($query) => $query->where(fn ($search) => $search
                ->where('name', 'like', "%$word%")
                ->orWhere('phone', 'like', "%$word%")
                ->when($normalizedPhone, fn ($phoneSearch) => $phoneSearch
                    ->orWhere('normalized_phone', 'like', "%$normalizedPhone%"))));
    }
}
