<?php

namespace App\Modules\Blog\Infrastructure\Persistence\Models\BlogCategory;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'blog_id',
        'category_id',
    ];
    protected $table = 'blog_categories';
}
