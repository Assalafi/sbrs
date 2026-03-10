<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class RemedialExamResult extends Model
{
    use HasUuid;

    protected $fillable = [
        'remedial_application_id',
        'exam_category',
        'subject',
        'grade',
    ];

    public function remedialApplication()
    {
        return $this->belongsTo(RemedialApplication::class);
    }
}
