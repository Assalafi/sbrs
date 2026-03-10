<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Student extends Authenticatable
{
    use HasUuid;

    protected $guard = 'student';

    protected $fillable = [
        'applicant_id',
        'registration_number',
        'academic_session_id',
        'programme_id',
        'subject_combination_id',
        'programme_type',
        'password',
        'surname',
        'first_name',
        'middle_name',
        'email',
        'phone',
        'passport_photo',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality',
        'state_of_origin',
        'lga',
        'home_address',
        'guardian_name',
        'guardian_address',
        'guardian_email',
        'guardian_phone',
        'sponsor_name',
        'sponsor_relationship',
        'sponsor_address',
        'group',
        'hall',
        'room_number',
        'health_status',
        'disability_type',
        'medication_type',
        'hobbies',
        'screening_status',
        'screening_remarks',
        'screened_by',
        'screened_at',
        'is_registered',
        'registered_at',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'screened_at' => 'datetime',
        'registered_at' => 'datetime',
        'is_registered' => 'boolean',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }

    public function subjectCombination()
    {
        return $this->belongsTo(SubjectCombination::class);
    }

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function courseRegistrations()
    {
        return $this->hasMany(CourseRegistration::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function screenedByUser()
    {
        return $this->belongsTo(User::class, 'screened_by');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->surname} {$this->first_name} {$this->middle_name}");
    }

    public function hasPaidRegistrationFee(): bool
    {
        return $this->payments()
            ->where('payment_type', 'registration')
            ->where('status', 'successful')
            ->exists();
    }

    public function hasPaidExamFee(): bool
    {
        return $this->payments()
            ->where('payment_type', 'examination')
            ->where('status', 'successful')
            ->exists();
    }

    public static function generateRegistrationNumber(string $programmeType): string
    {
        $session = AcademicSession::current();
        $year2 = $session ? substr($session->name, 2, 2) : substr(date('Y'), 2, 2);
        $code = $programmeType === 'IJMB' ? 'IJ' : 'RS';
        $prefix = "SBRS/{$code}/{$year2}";

        $last = self::where('registration_number', 'like', "{$prefix}/%")
            ->orderBy('registration_number', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->registration_number, -6);
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return "{$prefix}/{$newNumber}";
    }

    public static function createFromApplicant(Applicant $applicant, string $hashedPassword): self
    {
        $appData = $applicant->programme_type === 'IJMB'
            ? $applicant->ijmbApplication
            : $applicant->remedialApplication;

        return self::create([
            'applicant_id' => $applicant->id,
            'registration_number' => self::generateRegistrationNumber($applicant->programme_type),
            'academic_session_id' => $applicant->academic_session_id,
            'programme_id' => $applicant->programme_id,
            'subject_combination_id' => $applicant->subject_combination_id,
            'programme_type' => $applicant->programme_type,
            'password' => $hashedPassword,
            'surname' => $applicant->surname,
            'first_name' => $applicant->first_name,
            'middle_name' => $applicant->other_names,
            'email' => $applicant->email,
            'phone' => $applicant->phone,
            'passport_photo' => $applicant->passport_photo,
            'date_of_birth' => $appData?->date_of_birth,
            'gender' => $appData?->gender,
            'marital_status' => $appData?->marital_status,
            'nationality' => $appData?->nationality ?? 'Nigerian',
            'state_of_origin' => $appData?->state_of_origin,
            'lga' => $appData?->lga,
            'home_address' => $appData?->permanent_address ?? $appData?->correspondence_address,
        ]);
    }
}
