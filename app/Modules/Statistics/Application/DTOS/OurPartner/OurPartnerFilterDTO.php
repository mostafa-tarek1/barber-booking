<?php

namespace App\Modules\Statistics\Application\DTOS\OurPartner;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class OurPartnerFilterDTO extends BaseDTOAbstract
{
    protected bool $excludeAttributes = true;
    public $our_partner_id;
    public $title_en;
    public $title_ar;
    public $image;
    public $word;
    public $limit;
    public $paginate;
    public $created_by;

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
