<?php

namespace App\Modules\Rooms\Application\DTOS\RoomLocation;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class RoomLocationDTO extends BaseDTOAbstract
{
    public $room_id;
    public $room_location_id;
    public $translations;
    public $parent_id;
    public $created_by;
    public $updated_by;
    public $price=0;
    public $slug;
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
