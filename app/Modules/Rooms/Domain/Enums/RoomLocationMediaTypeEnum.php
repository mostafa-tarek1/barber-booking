<?php

namespace App\Modules\Rooms\Domain\Enums;

use App\Modules\Base\Domain\Traits\HasMetadata;

enum RoomLocationMediaTypeEnum: int
{
    use HasMetadata;

    case SLIDER = 1;
    case GALLERY = 2;
    case REEL = 3;

    public function getMetadata(): array
    {
        return match ($this) {
            self::SLIDER => [
                'label' => 'Slider',
                'value' => 1
            ],
            self::GALLERY => [
                'label' => 'Gallery',
                'value' => 2
            ],
            self::REEL => [
                'label' => 'Reel',
                'value' => 3
            ],
            default => [
                'label' => 'Unknown',
                'value' => 0
            ]
        };
    }
}
