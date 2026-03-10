<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class RemedialEmploymentRecord extends Model
{
    use HasUuid;

    protected $fillable = [
        'remedial_application_id',
        'employer',
        'post',
        'date_from',
        'date_to',
        'from_date',
        'to_date',
    ];

    public function remedialApplication()
    {
        return $this->belongsTo(RemedialApplication::class);
    }
}
