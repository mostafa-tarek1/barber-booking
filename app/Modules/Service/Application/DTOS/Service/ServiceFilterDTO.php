<?php

namespace App\Modules\Service\Application\DTOS\Service;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class ServiceFilterDTO extends BaseDTOAbstract
{
    protected bool $excludeAttributes = true;
    public $service_id;
    public $word;
    public $price;
    public $duration;
    public $name;
    public $status;
    public $limit;
    public $paginate;
    public $created_by;
    public $is_active;
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
            'is_parent'
        ]; // Default empty array
    }
}
