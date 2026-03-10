<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class RemedialApplication extends Model
{
    use HasUuid;

    protected $fillable = [
        'applicant_id',
        'academic_session_id',
        'date_of_birth',
        'gender',
        'marital_status',
        'state_of_origin',
        'lga',
        'correspondence_address',
        'guardian_name',
        'guardian_address',
        'guardian_phone',
        'guardian_email',
        'permanent_address',
        'permanent_phone',
        'permanent_email',
        'primary_school_name',
        'primary_school_from',
        'primary_school_to',
        'exam_date',
        'exam_centre',
        'exam_number',
        'sponsor_type',
        'sponsor_name',
        'sponsor_address',
        'games',
        'hobbies',
        'other_activities',
        'positions_held',
        'declaration_confirmed',
        'declaration_name',
        'declaration_date',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'declaration_date' => 'date',
        'declaration_confirmed' => 'boolean',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function institutions()
    {
        return $this->hasMany(RemedialInstitution::class);
    }

    public function examResults()
    {
        return $this->hasMany(RemedialExamResult::class);
    }

    public function employmentRecords()
    {
        return $this->hasMany(RemedialEmploymentRecord::class);
    }

    public function referees()
    {
        return $this->hasMany(RemedialReferee::class);
    }
}
