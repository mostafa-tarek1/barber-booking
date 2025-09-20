<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Models\Term;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;


class Term extends Model implements TranslatableContract
{
    use HasFactory , Translatable;
    protected $guarded = ['id'];

    public $translatedAttributes = ['title', 'description', 'subtitle'];
    protected $translationForeignKey = 'term_id';
    protected $table = 'terms';

}
