<?php

namespace App\Modules\Blog\Infrastructure\Persistence\Models\Category;


use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\Blog\Infrastructure\Persistence\Models\Blog\Blog;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;

class Category extends Model implements TranslatableContract
{
    use HasFactory;
    use Translatable;
    protected $fillable = [
        'image',
        'organization_id',
    ];
    public $translatedAttributes = ['title'];
    protected $translationForeignKey = 'category_id';
    protected $table = 'categories';
    protected $appends  = ["image_link"];


    public function getImageLinkAttribute()
    {
        return $this->image ? asset($this->image) : '';
    }

    public function blogs()
    {
        return $this->belongsToMany(Blog::class, 'blog_categories', 'category_id', 'blog_id');
    }
}
