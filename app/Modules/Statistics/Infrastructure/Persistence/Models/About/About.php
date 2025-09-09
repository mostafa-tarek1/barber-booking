<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Models\About;

use App\Modules\Statistics\Infrastructure\Persistence\Models\About\AboutWorkingHour\AboutWorkingHour;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Illuminate\Support\Facades\Storage;



class About extends Model implements TranslatableContract
{
    use HasFactory, Translatable;
    protected $guarded = [];

    public $translatedAttributes = ['title', 'description', 'location',];
    protected $translationForeignKey = 'about_id';
    protected $table = 'about';
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
    public function socials(): HasMany
    {
        return $this->hasMany(AboutSocial::class);
    }
    public function phones(): HasMany
    {
        return $this->hasMany(AboutPhone::class);
    }
    public function emails(): HasMany
    {
        return $this->hasMany(AboutEmail::class);
    }
    public function workingHours()
    {
        return $this->hasMany(AboutWorkingHour::class);
    }
}
