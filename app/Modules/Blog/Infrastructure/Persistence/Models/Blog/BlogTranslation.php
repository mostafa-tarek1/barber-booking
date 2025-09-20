<?php

namespace App\Modules\Blog\Infrastructure\Persistence\Models\Blog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogTranslation extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'blog_translations';
    protected $fillable = [
        'title',
        'subtitle',
        'description',
    ];
}
