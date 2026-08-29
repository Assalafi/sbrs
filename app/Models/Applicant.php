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
     * Payment types due for this applicant (active, matching programme + session,
     * payer type applicant/both, not fully paid).
     */
    public function duePaymentTypes()
    {
        $sessionId = $this->academic_session_id;

        $types = PaymentType::query()
            ->where('is_active', true)
            ->where(function ($q) use ($sessionId) {
                $q->where('academic_session_id', $sessionId)
                    ->orWhereNull('academic_session_id');
            })
            ->where(function ($q) {
                $q->where('programme_type', $this->programme_type)
                    ->orWhere('programme_type', 'all');
            })
            ->whereIn('payer_type', ['applicant', 'both'])
            ->orderBy('sort_order')
            ->get();

        return $types->filter(function ($type) {
            return !$this->paymentTypeProgress($type)['fully_paid'];
        })->values();
    }

    /**
     * Progress of a payment type for this applicant (shared logic with Student).
     */
    public function paymentTypeProgress($paymentType): array
    {
        $fullAmount = (float) $paymentType->amount;
        $payments = $this->payments()
            ->where(function ($q) use ($paymentType) {
                $q->where('payment_type', $paymentType->code)
                    ->orWhere('payment_type_id', $paymentType->id);
            })
            ->where('status', 'successful')
            ->get();

        $paid = $payments->sum('amount');
        $legacyFull = $payments->contains(function ($p) {
            return $p->is_full_payment || $p->installment === null;
        });

        if ($legacyFull || $paid >= $fullAmount) {
            return [
                'paid' => $fullAmount,
                'full_amount' => $fullAmount,
                'remaining' => 0,
                'fully_paid' => true,
                'installment' => null,
                'installment_label' => null,
                'installment_amount' => null,
                'installment_total' => null,
            ];
        }

        $installment = null;
        $installmentAmount = null;
        $installmentLabel = null;
        $installmentTotal = null;

        if ($paymentType->split_enabled && $paymentType->installment_count > 1) {
            $usedInstallments = $payments->whereNotNull('installment')->pluck('installment')->all();
            for ($i = 1; $i <= $paymentType->installment_count; $i++) {
                if (!in_array($i, $usedInstallments)) {
                    $installment = $i;
                    break;
                }
            }
            $installmentTotal = $paymentType->installment_count;
            if ($installment !== null) {
                if ($installment === 1) {
                    $installmentAmount = (float) ($paymentType->first_installment_amount ?: $fullAmount);
                    $installmentLabel = 'First Installment (' . rtrim(rtrim((string) ($paymentType->split_percent ?: (100 / $paymentType->installment_count)), '0'), '.') . '%)';
                } else {
                    $installmentAmount = round($fullAmount - $paid, 2);
                    $installmentLabel = 'Installment ' . $installment . ' of ' . $paymentType->installment_count;
                }
            }
        }

        return [
            'paid' => $paid,
            'full_amount' => $fullAmount,
            'remaining' => round($fullAmount - $paid, 2),
            'fully_paid' => false,
            'installment' => $installment,
            'installment_label' => $installmentLabel,
            'installment_amount' => $installmentAmount,
            'installment_total' => $installmentTotal,
        ];
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
