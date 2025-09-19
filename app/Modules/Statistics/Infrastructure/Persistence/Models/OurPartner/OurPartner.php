<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Models\OurPartner;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;



class OurPartner extends Model implements TranslatableContract
{
    use Translatable, HasFactory;

    public $translatedAttributes = ['title'];
    protected $table = 'our_partners';

    protected $fillable = [
        'image'
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
}
