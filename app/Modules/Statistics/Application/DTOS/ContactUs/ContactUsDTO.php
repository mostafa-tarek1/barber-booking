<?php

namespace App\Modules\Statistics\Application\DTOS\ContactUs;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;
class ContactUsDTO extends BaseDTOAbstract
{
    public $name;
    public $phone;
    public $email;
    public $message;
    public $contact_us_id;
    public ?int $per_page = null;
    public $word = null;


    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
