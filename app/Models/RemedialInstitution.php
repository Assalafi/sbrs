<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class RemedialInstitution extends Model
{
    use HasUuid;

    protected $fillable = [
        'remedial_application_id',
        'institution_name',
        'date_from',
        'date_to',
        'from_year',
        'to_year',
        'qualification',
    ];

    public function remedialApplication()
    {
        return $this->belongsTo(RemedialApplication::class);
    }
}
