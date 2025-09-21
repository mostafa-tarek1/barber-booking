<?php

namespace App\Modules\Service\Infrastructure\Persistence\Models\Service;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model implements TranslatableContract
{
    use Translatable;
    use HasFactory;

    public $translatedAttributes = [];

    protected $table = 'services';
    protected $fillable = [];
}
