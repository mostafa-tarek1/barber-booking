<?php

namespace App\Modules\Service\Http\Controllers\Api\Package;

use App\Http\Controllers\Controller;
use App\Modules\Service\Http\Resources\PackageResource;
use App\Modules\Service\Infrastructure\Persistence\Models\Package\Package;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::query()
            ->where('is_active', true)
            ->whereHas('services', fn ($query) => $query->where('services.is_active', true))
            ->whereDoesntHave('services', fn ($query) => $query->where('services.is_active', false))
            ->with(['services' => fn ($query) => $query->where('services.is_active', true), 'services.translations'])
            ->get();

        return response()->json([
            'status' => 200,
            'message' => 'Packages fetched successfully',
            'data' => PackageResource::collection($packages),
        ]);
    }
}
