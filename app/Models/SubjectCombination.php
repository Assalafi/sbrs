<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class SubjectCombination extends Model
{
    use HasUuid, Auditable;

    protected $fillable = [
        'programme_id',
        'name',
        'code',
        'subjects',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
