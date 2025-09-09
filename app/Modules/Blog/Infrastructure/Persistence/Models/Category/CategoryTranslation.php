<?php

namespace App\Modules\Blog\Infrastructure\Persistence\Models\Category;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoryTranslation extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'category_translations';
    protected $fillable = [
        'category_id',
        'locale',
        'title',
    ];
}
