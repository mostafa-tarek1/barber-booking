<?php

namespace App\Modules\Blog\Infrastructure\Persistence\Models\Category;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Blog\Infrastructure\Persistence\Models\Blog\Blog;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;


class Category extends Model implements TranslatableContract
{
    use Translatable;
    use HasFactory;

    public $translatedAttributes = ['title'];

    protected $table = 'categories';
    protected $fillable = [
        'is_active',
        'image',
        'slug',
        'parent_id',
        'created_by',
        'updated_by',
    ];

    public function ImageLink(): Attribute
    {
        if (isset($this->image) && Storage::disk('public')->exists($this->image)) {
            return Attribute::make(fn() => asset('storage/' . $this->image));
        } else {
            return Attribute::make(fn() => "");
        }
    }
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function blogs()
    {
        return $this->belongsToMany(Blog::class);
    }
}
