<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Models\About\AboutWorkingHour;

use App\Modules\Statistics\Infrastructure\Persistence\Models\About\About;
use Illuminate\Database\Eloquent\Model;

class AboutWorkingHour extends Model
{
    protected $table = 'about_working_hours';

    protected $fillable = [
        'about_id',
        'days',
        'from_time',
        'to_time'
    ];
    protected $casts = [
        'days' => 'array', 
    ];

    public function about()
    {
        return $this->belongsTo(About::class);
    }
}
