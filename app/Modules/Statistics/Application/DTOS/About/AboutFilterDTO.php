<?php

namespace App\Modules\Statistics\Application\DTOS\About;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class AboutFilterDTO extends BaseDTOAbstract
{
    protected bool $excludeAttributes = true;
    public $about_id;
    public $title;
    public $description;
    public $word;
    protected ?string $image;
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
