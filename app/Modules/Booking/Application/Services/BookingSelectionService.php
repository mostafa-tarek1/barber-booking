<?php

namespace App\Modules\Booking\Application\Services;

use App\Modules\Service\Infrastructure\Persistence\Models\Package\Package;
use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BookingSelectionService
{
    public function resolve(array $serviceIds, array $packageIds): array
    {
        $services = $this->activeServices($serviceIds);
        $packages = $this->activePackages($packageIds);
        $serviceRows = $services->map(fn (Service $service) => $this->serviceRow($service));

        foreach ($packages as $package) {
            foreach ($package->services as $service) {
                $serviceRows->push($this->serviceRow(
                    $service,
                    $package->id,
                    (int) $service->pivot->quantity
                ));
            }
        }

        return [
            'service_rows' => $serviceRows->values(),
            'packages' => $packages,
            'duration' => (int) $serviceRows->sum(
                fn (array $row) => $row['duration_snapshot'] * $row['quantity']
            ),
            'total_price_cents' => $this->totalPriceCents($serviceRows, $packages),
        ];
    }

    private function totalPriceCents(Collection $serviceRows, Collection $packages): int
    {
        $directServices = $serviceRows
            ->whereNull('package_id')
            ->sum(fn (array $row) => (float) $row['price_snapshot'] * $row['quantity']);
        $packageTotal = $packages->sum(fn (Package $package) => (float) $package->price);

        return (int) round(($directServices + $packageTotal) * 100);
    }

    private function activeServices(array $serviceIds): Collection
    {
        $ids = collect($serviceIds)->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $services = Service::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->with('translations')
            ->get();
        if ($services->count() !== $ids->count()) {
            throw ValidationException::withMessages(['services' => 'إحدى الخدمات غير متاحة.']);
        }

        return $services;
    }

    private function activePackages(array $packageIds): Collection
    {
        $ids = collect($packageIds)->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $packages = Package::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->with([
                'translations',
                'services.translations',
            ])
            ->get();
        if ($packages->count() !== $ids->count() || $packages->contains(fn (Package $package) => $package->services->isEmpty())) {
            throw ValidationException::withMessages(['packages' => 'إحدى الباقات غير متاحة.']);
        }

        return $packages;
    }

    private function serviceRow(Service $service, ?int $packageId = null, int $quantity = 1): array
    {
        return [
            'service_id' => $service->id,
            'package_id' => $packageId,
            'name_snapshot' => $service->translate('ar')?->name ?: $service->name,
            'price_snapshot' => $service->price,
            'duration_snapshot' => $service->duration,
            'quantity' => $quantity,
        ];
    }
}
