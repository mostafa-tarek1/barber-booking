<?php

namespace App\Modules\Finance\Domain\Enums;

enum ExpenseCategoryEnum: string
{
    case Internet = 'internet';
    case Electricity = 'electricity';
    case Supplies = 'supplies';
    case Meals = 'meals';
    case Salaries = 'salaries';
    case Team = 'team';
    case Rent = 'rent';
    case Maintenance = 'maintenance';
    case Marketing = 'marketing';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Internet => 'إنترنت',
            self::Electricity => 'كهرباء',
            self::Supplies => 'مستلزمات',
            self::Meals => 'وجبات وضيافة',
            self::Salaries => 'مرتبات',
            self::Team => 'مصروفات الفريق',
            self::Rent => 'إيجار',
            self::Maintenance => 'صيانة',
            self::Marketing => 'تسويق',
            self::Other => 'أخرى',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $category) => ['category' => $category->value, 'label' => $category->label()],
            self::cases()
        );
    }
}
