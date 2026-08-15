<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ARABIC_NAMES_BY_PRICE = [
        '1000.00' => 'باقة العريس الأساسية',
        '1500.00' => 'باقة العريس الملكية',
        '2000.00' => 'باقة ملك العرسان',
    ];

    public function up(): void
    {
        foreach (self::ARABIC_NAMES_BY_PRICE as $price => $arabicName) {
            $this->renamePackage($price, 'GROOM PACKAGE', $arabicName);
        }
    }

    public function down(): void
    {
        foreach (self::ARABIC_NAMES_BY_PRICE as $price => $arabicName) {
            $this->renamePackage($price, $arabicName, 'GROOM PACKAGE');
        }
    }

    private function renamePackage(string $price, string $currentName, string $newName): void
    {
        DB::table('package_translations')
            ->where('locale', 'ar')
            ->where('name', $currentName)
            ->whereIn('package_id', DB::table('packages')->select('id')->where('price', $price))
            ->update(['name' => $newName, 'updated_at' => now()]);
    }
};
