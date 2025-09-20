<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Models\ContactUs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    use HasFactory;

    protected $table = 'contact_us';
    protected $fillable = [
        'name',
        'phone',
        'email',
        'message'
    ];
}
