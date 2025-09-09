<?php

namespace App\Modules\Statistics\Application\DTOS\Term;


use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

use App\Modules\Base\Domain\DTO\BaseDTOInterface;

class TermDTO extends BaseDTOAbstract
{
    protected ?string $title_en = null;
    protected ?string $title_ar = null;
    protected ?string $description_en = null;
    protected ?string $description_ar = null;
    protected ?string $subtitle_en = null;
    protected ?string $subtitle_ar = null;
    public int $term_id ;
    protected ?int $per_page = null;
    protected ?string $word = null;


    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
