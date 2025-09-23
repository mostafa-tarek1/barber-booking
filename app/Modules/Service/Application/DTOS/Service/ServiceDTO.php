<?php

namespace App\Modules\Service\Application\DTOS\Service;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class ServiceDTO extends BaseDTOAbstract
{
    protected bool $excludeAttributes = true;
    public $translations;
    public $name;
    public $price;
    public $duration;
    public $description;
    public $image;
    public $created_by;
    public $updated_by;
    public $is_active = true;
    protected string $imageFolder = 'service'; // fallback folder
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
