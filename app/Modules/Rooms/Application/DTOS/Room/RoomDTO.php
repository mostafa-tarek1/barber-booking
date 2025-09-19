<?php

namespace App\Modules\Rooms\Application\DTOS\Room;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class RoomDTO extends BaseDTOAbstract
{
    public $room_id;
    public $translations;
    public $parent_id;
    public $created_by;
    public $updated_by;
    public $price;
    public $image;
    public $slug;
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
