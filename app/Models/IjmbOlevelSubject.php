<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class IjmbOlevelSubject extends Model
{
    use HasUuid;

    protected $fillable = [
        'ijmb_olevel_result_id',
        'subject',
        'grade',
    ];

    public function olevelResult()
    {
        return $this->belongsTo(IjmbOlevelResult::class, 'ijmb_olevel_result_id');
    }
}
