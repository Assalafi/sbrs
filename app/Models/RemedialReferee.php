<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class RemedialReferee extends Model
{
    use HasUuid;

    protected $fillable = [
        'remedial_application_id',
        'name',
        'address',
    ];

    public function remedialApplication()
    {
        return $this->belongsTo(RemedialApplication::class);
    }
}
