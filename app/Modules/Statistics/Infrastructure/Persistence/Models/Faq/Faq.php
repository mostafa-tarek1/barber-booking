<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Models\Faq;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;



class Faq extends Model implements TranslatableContract
{
    use Translatable, HasFactory;

    public $translatedAttributes = ['question', 'answer'];
    protected $table = 'faqs';

        protected $fillable = [
        'is_active',
        'order',
        'is_active' => 'boolean',
        'order' => 'integer',
        'created_by',
        // 'updated_by',
    ];
}
