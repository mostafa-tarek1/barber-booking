<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Models\Privacy;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;


class Privacy extends Model implements TranslatableContract
{
    use HasFactory , Translatable;
    protected $guarded = ['id'];
    protected $table = 'privacy';
    public $translatedAttributes = ['title', 'description', 'subtitle'];
    protected $translationForeignKey = 'privacy_id';
}
