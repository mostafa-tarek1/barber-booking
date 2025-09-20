<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Models\Slider;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Slider extends Model implements TranslatableContract
{
    use Translatable;
    use HasFactory;

    public $translatedAttributes = ['title', 'description', 'sub_title'];
    protected $table = 'sliders';
    protected $fillable = ['image'];

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
}
