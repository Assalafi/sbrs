<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasUuid, Auditable;

    protected $fillable = [
        'programme_id',
        'subject_combination_id',
        'course_code',
        'course_title',
        'credit_units',
        'semester',
        'is_active',
    ];

    protected $casts = [
        'credit_units' => 'integer',
        'is_active' => 'boolean',
    ];

    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }

    public function subjectCombination()
    {
        return $this->belongsTo(SubjectCombination::class);
    }

    public function registrations()
    {
        return $this->hasMany(CourseRegistration::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }
}
