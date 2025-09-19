<?php

namespace App\Modules\Rooms\Application\DTOS\RoomLocationMedia;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class RoomLocationMediaFilterDTO extends BaseDTOAbstract
{
    protected bool $excludeAttributes = true;
    public $room_location_id;
    public $room_location_media_id;
    public $title;
    public $word;
    public $limit;
    public $paginate;
    public $created_by;
    public $duration_id;
    public string $type;
    public string $file;
    public ?string $alt;
    public string $file_type;
    public bool $status = true;


    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function excludedAttributes(): array
    {
        return [
            'excludeAttributes',
            'limit',
            'paginate',
            'word',
        ]; // Default empty array
    }
}
