<?php
namespace App\Modules\Statistics\Infrastructure\Persistence\Models\About\AboutUs;

use App\Modules\Statistics\Infrastructure\Persistence\Models\About\AboutUsImage\AboutUsImage;
use App\Modules\Statistics\Infrastructure\Persistence\Models\About\AboutUsStatistics\AboutUsStatistic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class AboutUs extends Model implements TranslatableContract
{
    use HasFactory, Translatable;
    protected $table = 'about_us';
    protected $translationForeignKey = 'about_us_id';

    public $translatedAttributes = ['title', 'description', 'sub_title', 'our_mission', 'our_vision', 'our_goal'];

    protected $fillable = [
        'main_image',
    ];
    public function imageLink(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->main_image && Storage::disk('public')->exists($this->main_image)) {
                    return asset('storage/' . $this->main_image);
                }
                return "";
            }
        );
    }
    public function statistics()
    {
        return $this->hasMany(AboutUsStatistic::class);
    }
    public function images()
    {
        return $this->hasMany(AboutUsImage::class);
    }
}
