<?php

namespace App\Modules\Blog\Infrastructure\Persistence\Models\Blog;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\Blog\Infrastructure\Persistence\Models\Hashtag\Hashtag;
use App\Modules\Blog\Infrastructure\Persistence\Models\Category\Category;
use App\Modules\Organization\Infrastructure\Persistence\Models\Employee;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;

class Blog extends Model implements TranslatableContract
{
    use HasFactory;
    use Translatable;
    protected $fillable = [
        'image',
        'organization_id',
        'slug',
        'author',

    ];
    public $translatedAttributes = ['title', 'subtitle', 'description'];
    protected $translationForeignKey = 'blog_id';
    protected $table = 'blogs';
    protected $appends  = ["image_link"];


    public function getImageLinkAttribute()
    {
        return $this->image ? asset("storage/" .$this->image) : '';
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($blog) {
            if (empty($blog->slug)) {
                $blog->slug = $blog->generateSlug();
                $blog->save();
            }
        });

        static::updating(function ($blog) {
            // Optional: regenerate slug if title in current locale changed
            if ($blog->isDirty('title')) {
                $blog->slug = $blog->generateSlug();
            }
        });
    }




    public function generateSlug()
    {
        // Try to get title from current locale
        $title = $this->getTranslation('title', app()->getLocale())->title;
        // dd($title);
        // If not found, fallback to first available locale
        if (!$title) {
            foreach (config('translatable.locales', ['en', 'ar']) as $locale) {
                $title = $this->getTranslation('title', $locale);
                if ($title) break;
            }
        }

        // Final fallback if no title exists at all
        if (!$title) {
            $title = 'blog';
        }

        // Convert to a slug (e.g., "Wear Fasion" → "wear-fasion")
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;

        $i = 1;
        while (
            self::where('slug', $slug)
            ->where('id', '!=', $this->id)
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $i++;
        }

        return $slug;
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'blog_categories', 'blog_id', 'category_id');
    }

    public function hashtags()
    {
        return $this->belongsToMany(Hashtag::class, 'blog_hashtags', 'blog_id', 'hashtag_id');
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
    public function updater()
    {
        return $this->belongsTo(Employee::class, 'updated_by');
    }
}
