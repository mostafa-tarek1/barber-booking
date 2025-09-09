<?php

namespace App\Modules\Blog\Infrastructure\Persistence\Models\BlogHashtag;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogHashtag extends Model
{
    use HasFactory;
    protected $fillable = [
        'blog_id',
        'hashtag_id',
    ];
    protected $table = 'blog_hashtags';
}
