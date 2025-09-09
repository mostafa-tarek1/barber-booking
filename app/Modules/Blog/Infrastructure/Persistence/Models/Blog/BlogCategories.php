<?php

namespace App\Modules\Blog\Infrastructure\Persistence\Models\Blog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogCategories extends Model
{
    use HasFactory;

    protected $table = 'blog_categories';

    protected $fillable = [
        'blog_id',
        'category_id',
    ];
    public $incrementing = false;
    protected $primaryKey = ['blog_id', 'category_id'];
}
