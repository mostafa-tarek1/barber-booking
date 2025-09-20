<?php

namespace App\Modules\Statistics\Application\DTOS\ContactUs;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class ContactUsFilterDTO extends BaseDTOAbstract
{
    protected bool $excludeAttributes = true;
    public $contact_us_id;
    public $name;
    public $phone;
    public $email;
    public $message;
    public $word;
    public $parent_id;
    public $limit;
    public $paginate;

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
            'word'
        ]; // Default empty array
    }
}
