<?php

namespace App\Modules\Statistics\Domain\Enums;

use App\Modules\Base\Domain\Traits\HasMetadata;

enum SocialKeyEnum: int
{
    use HasMetadata;

    case youtube = 1;
    case instagram = 2;
    case facebook = 3;
    case linkedin = 4;
    case twitter = 5;
    case whatsapp = 6;
    case messenger = 7;
    case tiktok = 8;

    public static function tryFromLabel(string $label): ?self
    {
        return match ($label) {
            'youtube' => self::youtube,
            'instagram' => self::instagram,
            'facebook' => self::facebook,
            'linkedin' => self::linkedin,
            'twitter' => self::twitter,
            'whatsapp' => self::whatsapp,
            'messenger' => self::messenger,
            'tikTok' => self::tiktok,
            default => null,
        };
    }
    public function getMetadata(): array
    {
        return match ($this) {
            'youtube' => [
                'label' => 'Youtube',
                // 'label' => '1',
                'icon' => 'fab fa-youtube',
            ],
            'instagram' => [
                'label' => 'Instagram',
                // 'label' => '2',
                'icon' => 'fab fa-instagram',
            ],
            'facebook' => [
                'label' => 'Facebook',
                // 'label' => '3', 
                'icon' => 'fab fa-facebook',
            ],
            'linkedin' => [
                'label' => 'Linkedin',
                // 'label' => '4',
                'icon' => 'fab fa-linkedin',
            ],
            'twitter' => [
                'label' => 'Twitter',
                // 'label' => '5', 
                'icon' => 'fab fa-twitter',
            ],
            'whatsApp' => [
                'label' => 'WhatsApp',
                // 'label' => '6', 
                'icon' => 'fab fa-whatsapp',
            ],
            'messenger' => [
                'label' => 'Messenger',
                // 'label' => '7',
                'icon' => 'fab fa-facebook-messenger',
            ],
            'tikTok' => [
                'label' => 'TikTok',
                // 'label' => '8',
                'icon' => 'fab fa-tiktok',
            ]
        };
    }
}