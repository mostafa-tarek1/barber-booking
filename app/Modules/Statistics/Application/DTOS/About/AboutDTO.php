<?php

namespace App\Modules\Statistics\Application\DTOS\About;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

/** @var array<AboutSocialsDto> */

class AboutDTO extends BaseDTOAbstract
{
    public $title_en;
    public $title_ar;
    public $description_en;
    public $description_ar;
    public $location_en;
    public $location_ar;
    public $translations;
    public $lat;
    public $lng;
    public $working_hours = [];
    public ?int $about_id;
    public ?string $image;
    public $phones = [];
    public $emails = [];
    public $socials = [];

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function excludedAttributes(): array
    {
        return [
            'phones',
            'emails',
            'socials',
            'working_hours'
        ]; // Default empty array
    }
}
