<?php

namespace App\Modules\Rooms\Application\DTOS\RoomAdditional;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class RoomAdditionalDTO extends BaseDTOAbstract
{

    public $room_additional_id;
    public $room_id;
    public $id = null;
    public $parent_id = null;
    public $image = null;
    public bool $is_active = true;
    public $order = 0;
    public $created_by;
    public $updated_by;
    protected string $imageFolder = 'additional_images'; // fallback folder

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
