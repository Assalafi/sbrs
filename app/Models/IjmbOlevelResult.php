<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class IjmbOlevelResult extends Model
{
    use HasUuid;

    protected $fillable = [
        'ijmb_application_id',
        'exam_type',
        'examination_type_other',
        'exam_number',
        'exam_year',
        'exam_centre',
    ];

    public function ijmbApplication()
    {
        return $this->belongsTo(IjmbApplication::class);
    }

    public function subjects()
    {
        return $this->hasMany(IjmbOlevelSubject::class);
    }
}
