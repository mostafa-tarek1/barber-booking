<?php

namespace App\Modules\Service\Http\Controllers\Dashboard\Package;

use App\Http\Controllers\Controller;
use App\Modules\Service\Http\Resources\PackageResource;
use App\Modules\Service\Infrastructure\Persistence\Models\Package\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::query()->with('services.translations')->latest()->get();

        return response()->json([
            'status' => 200,
            'message' => 'Packages fetched successfully',
            'data' => PackageResource::collection($packages),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePackage($request);
        $package = DB::transaction(fn () => $this->persistPackage(new Package, $validated, $request));

        return response()->json([
            'status' => 201,
            'message' => 'Package created successfully',
            'data' => PackageResource::make($package),
        ], 201);
    }

    public function update(Request $request)
    {
        $validated = $this->validatePackage($request, true);
        $package = Package::query()->findOrFail($validated['package_id']);
        $package = DB::transaction(fn () => $this->persistPackage($package, $validated, $request));

        return response()->json([
            'status' => 200,
            'message' => 'Package updated successfully',
            'data' => PackageResource::make($package),
        ]);
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate(['package_id' => ['required', 'integer', 'exists:packages,id']]);
        $package = Package::query()->findOrFail($validated['package_id']);
        $package->update(['is_active' => false]);

        return response()->json(['status' => 200, 'message' => 'Package disabled successfully', 'data' => null]);
    }

    private function validatePackage(Request $request, bool $updating = false): array
    {
        $rules = [
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'services' => ['required', 'array', 'min:1'],
            'services.*.service_id' => ['required', 'integer', 'distinct', 'exists:services,id'],
            'services.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'image' => ['nullable', 'image', 'max:4096'],
            'translations' => ['required', 'array'],
        ];
        if ($updating) {
            $rules['package_id'] = ['required', 'integer', 'exists:packages,id'];
        }
        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $rules["translations.name_$locale"] = ['required', 'string', 'min:2', 'max:255'];
            $rules["translations.description_$locale"] = ['nullable', 'string', 'max:2000'];
        }

        return $request->validate($rules);
    }

    private function persistPackage(Package $package, array $validated, Request $request): Package
    {
        $attributes = [
            'price' => $validated['price'],
            'is_active' => $validated['is_active'],
        ];
        if ($request->hasFile('image')) {
            if ($package->image) {
                Storage::disk('public')->delete($package->image);
            }
            $attributes['image'] = $request->file('image')->store('packages', 'public');
        }
        $package->fill($attributes)->save();

        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $translation = $package->translateOrNew($locale);
            $translation->name = $validated['translations']["name_$locale"];
            $translation->description = $validated['translations']["description_$locale"] ?? null;
        }
        $package->save();

        $services = collect($validated['services'])->mapWithKeys(
            fn (array $service, int $index) => [
                $service['service_id'] => [
                    'quantity' => $service['quantity'],
                    'sort_order' => $index,
                ],
            ]
        )->all();
        $package->services()->sync($services);

        return $package->load('services.translations');
    }
}
