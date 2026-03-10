<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class IjmbReferee extends Model
{
    use HasUuid;

    protected $fillable = [
        'ijmb_application_id',
        'name',
        'address',
    ];

    public function ijmbApplication()
    {
        return $this->belongsTo(IjmbApplication::class);
    }
}
