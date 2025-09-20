<?php

namespace App\Modules\Statistics\Application\DTOS\Term;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class TermFilterDTO extends BaseDTOAbstract
{
    protected bool $excludeAttributes = true;
    public $term_id;
    public $title;
    public $description;
    public $word;
    public $parent_id;
    // public $user_id;
    public $status;
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
