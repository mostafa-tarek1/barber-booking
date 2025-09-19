<?php

namespace App\Modules\Rooms\Application\DTOS\RoomLocationMedia;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class RoomLocationMediaDTO extends BaseDTOAbstract
{
    public  $id;
    public $created_by;
    public $updated_by;
    public  $room_location_id;
    public  $room_location_media_id;
    public  $type;
    public $file;
    public  $alt;
    public  $title;
    public $file_type;
    public bool $status = true;

    protected string $imageFolder = 'location-media'; // fallback folder

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
