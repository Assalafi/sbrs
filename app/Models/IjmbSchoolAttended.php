<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class IjmbSchoolAttended extends Model
{
    use HasUuid;

    protected $table = 'ijmb_schools_attended';

    protected $fillable = [
        'ijmb_application_id',
        'school_name',
        'date_from',
        'date_to',
        'from_year',
        'to_year',
        'qualification',
    ];

    public function ijmbApplication()
    {
        return $this->belongsTo(IjmbApplication::class);
    }
}
