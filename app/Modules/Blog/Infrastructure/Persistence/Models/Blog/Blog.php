<?php

namespace App\Modules\Blog\Infrastructure\Persistence\Models\Blog;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Blog\Infrastructure\Persistence\Models\Category\Category;
use App\Modules\Blog\Infrastructure\Persistence\Models\Hashtag\Hashtag;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Blog extends Model implements TranslatableContract
{
    use Translatable;
    use HasFactory;

    public $translatedAttributes = ['title', 'subtitle', 'description'];

    protected $table = 'blogs';
    protected $fillable = [
        'slug',
        'image',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function imageLink(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->image && Storage::disk('public')->exists($this->image)) {
                    return asset('storage/' . $this->image);
                }
                return "";
            }
        );
    }
    public function hashtags()
    {
        return $this->belongsToMany(Hashtag::class, 'blog_hashtags')->withTimestamps();
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'blog_categories')->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
