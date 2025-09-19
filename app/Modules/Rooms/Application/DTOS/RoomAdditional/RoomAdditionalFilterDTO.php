<?php

namespace App\Modules\Rooms\Application\DTOS\RoomAdditional;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class RoomAdditionalFilterDTO extends BaseDTOAbstract
{
    protected bool $excludeAttributes = true;
    public $room_id;
    public $title;
    public $word;
    public $parent_id;
    public $limit;
    public $room_additional_id;
    public $paginate;
    public $created_by;
    public $duration_id;
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
