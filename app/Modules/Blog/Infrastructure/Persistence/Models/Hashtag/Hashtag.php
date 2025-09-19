<?php

namespace App\Modules\Blog\Infrastructure\Persistence\Models\Hashtag;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Blog\Infrastructure\Persistence\Models\Blog\Blog;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hashtag extends Model implements TranslatableContract
{
    use Translatable;
    use HasFactory;

    public $translatedAttributes = ['title',];

    protected $table = 'hashtags';
    protected $fillable = ['created_by', 'slug'];

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
    public function blogs()
    {
        return $this->belongsToMany(Blog::class , 'blog_hashtags');
    }
}

