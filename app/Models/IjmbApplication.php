<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class IjmbApplication extends Model
{
    use HasUuid;

    protected $fillable = [
        'applicant_id',
        'academic_session_id',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality',
        'state_of_origin',
        'lga',
        'permanent_address',
        'extracurricular_activities',
        'disability_or_sickness',
        'nok_name',
        'nok_phone',
        'nok_relationship',
        'nok_address',
        'sponsor_type',
        'sponsor_name',
        'sponsor_address',
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

    public function schoolsAttended()
    {
        return $this->hasMany(IjmbSchoolAttended::class);
    }

    public function olevelResults()
    {
        return $this->hasMany(IjmbOlevelResult::class);
    }

    public function referees()
    {
        return $this->hasMany(IjmbReferee::class);
    }
}
