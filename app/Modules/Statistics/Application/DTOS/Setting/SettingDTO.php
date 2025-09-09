<?php

namespace App\Modules\Statistics\Application\DTOS\Setting;


use App\Modules\Base\Domain\DTO\BaseDTOAbstract;
use App\Modules\Product\Domain\Enums\TaxTypeEnum;

class SettingDTO extends BaseDTOAbstract
{
    public int $setting_id ;
    public float $tax_amount;
    public  $tax_type;


    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
    
}
