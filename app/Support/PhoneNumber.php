<?php

namespace App\Support;

final class PhoneNumber
{
    private const DIGIT_TRANSLATIONS = [
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
    ];

    public static function normalize(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', strtr($phone, self::DIGIT_TRANSLATIONS));
        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 14 && str_starts_with($digits, '0020')) {
            return '0'.substr($digits, 4);
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '20')) {
            return '0'.substr($digits, 2);
        }

        return $digits;
    }
}
