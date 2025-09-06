<?php

namespace App\Modules\Auth\Application\DTOS\Permission;


use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

use App\Modules\Base\Domain\DTO\BaseDTOInterface;

class PermissionDTO extends BaseDTOAbstract
{
    public int $id;
    public $employee_id;
    public $permissions;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
