<?php

namespace App\Modules\Blog\Infrastructure\Persistence\Models\Blog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogHashtags extends Model
{
    use HasFactory;

    protected $table = 'blog_hashtags';

    protected $fillable = [
        'blog_id',
        'hashtag_id',
    ];
    public $incrementing = false;
    protected $primaryKey = ['blog_id', 'hashtag_id'];
}
