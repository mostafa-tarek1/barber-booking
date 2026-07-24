<?php

namespace Database\Seeders;

use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceCatalogSeeder extends Seeder
{
    private const SERVICES = [
        ['ar' => 'حلاقة شعر', 'en' => 'Haircut', 'price' => 100, 'duration' => 30],
        ['ar' => 'حلاقة ذقن', 'en' => 'Beard Shave', 'price' => 70, 'duration' => 20],
        ['ar' => 'سشوار', 'en' => 'Blow Dry', 'price' => 80, 'duration' => 30],
        ['ar' => 'حمام كريم', 'en' => 'Hair Cream Treatment', 'price' => 20, 'duration' => 20],
        ['ar' => 'ماسك للبشرة', 'en' => 'Face Mask', 'price' => 20, 'duration' => 20],
        ['ar' => 'جلسة بشرة 3 مراحل', 'en' => '3-Step Facial', 'price' => 150, 'duration' => 45],
        ['ar' => 'جلسة بشرة 7 مراحل', 'en' => '7-Step Facial', 'price' => 350, 'duration' => 75],
        ['ar' => 'جاكوزي', 'en' => 'Jacuzzi', 'price' => 700, 'duration' => 60],
        ['ar' => 'مساج', 'en' => 'Massage', 'price' => 500, 'duration' => 60],
        ['ar' => 'حجامة', 'en' => 'Hijama', 'price' => 500, 'duration' => 45],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            foreach (self::SERVICES as $serviceDefinition) {
                $this->seedService($serviceDefinition);
            }
        });
    }

    private function seedService(array $serviceDefinition): void
    {
        $service = Service::query()
            ->whereHas('translations', fn ($query) => $query
                ->where('locale', 'ar')
                ->where('name', $serviceDefinition['ar']))
            ->firstOrNew();

        $service->fill([
            'price' => $serviceDefinition['price'],
            'duration' => $serviceDefinition['duration'],
            'is_active' => true,
        ])->save();

        $this->saveTranslation($service, 'ar', $serviceDefinition['ar']);
        $this->saveTranslation($service, 'en', $serviceDefinition['en']);
    }

    private function saveTranslation(Service $service, string $locale, string $name): void
    {
        $translation = $service->translateOrNew($locale);
        $translation->name = $name;
        $translation->description = null;
        $translation->save();
    }
}
