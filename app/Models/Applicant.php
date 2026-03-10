<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Applicant extends Authenticatable
{
    use HasUuid;

    protected $guard = 'applicant';

    protected $fillable = [
        'application_number',
        'surname',
        'first_name',
        'other_names',
        'email',
        'phone',
        'password',
        'programme_type',
        'programme_id',
        'subject_combination_id',
        'academic_session_id',
        'passport_photo',
        'indigene_cert',
        'primary_cert',
        'ssce_cert',
        'birth_cert',
        'status',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

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

    public function ijmbApplication()
    {
        return $this->hasOne(IjmbApplication::class);
    }

    public function remedialApplication()
    {
        return $this->hasOne(RemedialApplication::class);
    }

    public function application()
    {
        if ($this->programme_type === 'IJMB') {
            return $this->ijmbApplication();
        }
        return $this->remedialApplication();
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->surname} {$this->first_name} {$this->other_names}");
    }

    public function hasPaidApplicationFee(): bool
    {
        return $this->payments()
            ->where('payment_type', 'application')
            ->where('status', 'successful')
            ->exists();
    }

    public function hasPaidAdmissionFee(): bool
    {
        return $this->payments()
            ->where('payment_type', 'admission')
            ->where('status', 'successful')
            ->exists();
    }

    public function getPendingPayment(string $type): ?Payment
    {
        return $this->payments()
            ->where('payment_type', $type)
            ->where('status', 'pending')
            ->latest()
            ->first();
    }

    /**
     * Get completion status for each application form section.
     */
    public function getSectionCompletion(): array
    {
        if ($this->programme_type === 'IJMB') {
            return $this->getIjmbSectionCompletion();
        }
        return $this->getRemedialSectionCompletion();
    }

    protected function getIjmbSectionCompletion(): array
    {
        $app = $this->ijmbApplication;
        $hasPersonal = $this->programme_id
            && $this->passport_photo
            && $this->indigene_cert
            && $this->primary_cert
            && $this->ssce_cert
            && $this->birth_cert
            && $app
            && $app->date_of_birth
            && $app->gender
            && $app->state_of_origin
            && $app->lga
            && $app->nok_name
            && $app->nok_phone;

        $hasSchools = $app && $app->schoolsAttended()->count() > 0;

        $hasResults = $app && $app->olevelResults()->whereHas('subjects')->count() > 0;

        $hasSponsorship = $app && $app->sponsor_type;

        $hasReferees = $app && $app->referees()->count() >= 3;

        return [
            'personal' => (bool) $hasPersonal,
            'schools' => (bool) $hasSchools,
            'results' => (bool) $hasResults,
            'sponsorship' => (bool) $hasSponsorship,
            'referees' => (bool) $hasReferees,
        ];
    }

    protected function getRemedialSectionCompletion(): array
    {
        $app = $this->remedialApplication;
        $hasPersonal = $this->programme_id
            && $this->passport_photo
            && $this->indigene_cert
            && $this->primary_cert
            && $this->ssce_cert
            && $this->birth_cert
            && $app
            && $app->date_of_birth
            && $app->gender
            && $app->state_of_origin
            && $app->lga
            && $app->guardian_name
            && $app->guardian_phone;

        $hasInstitutions = $app && $app->institutions()->count() > 0;

        $hasResults = $app && $app->examResults()->count() > 0 && $app->exam_number;

        $hasSponsorship = $app && $app->sponsor_type;

        $hasReferees = $app && $app->referees()->count() >= 3;

        return [
            'personal' => (bool) $hasPersonal,
            'schools' => (bool) $hasInstitutions,
            'results' => (bool) $hasResults,
            'sponsorship' => (bool) $hasSponsorship,
            'referees' => (bool) $hasReferees,
        ];
    }

    public static function generateApplicationNumber(): string
    {
        $session = AcademicSession::current();
        $year2 = $session ? substr($session->name, 2, 2) : substr(date('Y'), 2, 2);
        $prefix = "SBRS/{$year2}";

        $lastApplicant = self::where('application_number', 'like', "{$prefix}/%")
            ->orderBy('application_number', 'desc')
            ->first();

        if ($lastApplicant) {
            $lastNumber = (int) substr($lastApplicant->application_number, -6);
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return "{$prefix}/{$newNumber}";
    }
}
