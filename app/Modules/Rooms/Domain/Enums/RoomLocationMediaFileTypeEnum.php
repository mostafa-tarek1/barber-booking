<?php

namespace App\Modules\Rooms\Domain\Enums;

use App\Modules\Base\Domain\Traits\HasMetadata;

enum RoomLocationMediaFileTypeEnum: int
{
    use HasMetadata;

    case IMAGE = 1;
    case VIDEO = 2;
    case AUDIO = 3;

    case OTHER = 4;

    public function getMetadata(): array
    {
        return match ($this) {
            self::IMAGE => [
                'label' => 'Image',
                'value' => 1
            ],
            self::VIDEO => [
                'label' => 'Video',
                'value' => 2
            ],
            self::AUDIO => [
                'label' => 'Audio',
                'value' => 3
            ],
            self::OTHER => [
                'label' => 'Other',
                'value' => 4
            ],
            default => [
                'label' => 'Unknown',
                'value' => 0
            ]
        };
    }
}
