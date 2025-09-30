<?php

namespace App\Modules\Notification\Application\DTOS\Notification;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class NotificationDTO extends BaseDTOAbstract
{
  public $notification_id;
  public $title;
  public $message;
  public $type;
  public $data;
  public $user_id;
  public $employee_id;
  public $created_by;
  public $updated_by;

  public function __construct(array $data = [])
  {
    parent::__construct($data);
  }

  public function uniqueAttributes(): array
  {
    return ['notification_id'];
  }
}
