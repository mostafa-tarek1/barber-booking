<?php

namespace App\Modules\Statistics\Application\DTOS\OurPartner;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;


class OurPartnerDTO extends BaseDTOAbstract
{
    protected string $title_en ;
    protected string $title_ar ;
    public  $our_partner_id ;
    public $image;
    protected int $per_page ;
    protected string $word ;
    protected int $order;
    public $updated_by;
    public $created_by;


    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
