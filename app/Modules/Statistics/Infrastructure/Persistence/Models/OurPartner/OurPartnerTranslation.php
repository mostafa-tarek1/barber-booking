<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Models\OurPartner;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OurPartnerTranslation extends Model
{
    use HasFactory;
    protected $table = 'our_partner_translations';
    protected $fillable = ['title', 'locale', 'our_partner_id'];
}
