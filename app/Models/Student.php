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

    public function hasPaidHostelFee(): bool
    {
        return $this->payments()
            ->where('payment_type', 'hostel')
            ->where('status', 'successful')
            ->exists();
    }

    /**
     * All payments applicable to this student against a given payment type.
     */
    public function paymentsForType($paymentType)
    {
        return $this->payments()
            ->where(function ($q) use ($paymentType) {
                $q->where('payment_type', $paymentType->code)
                    ->orWhere('payment_type_id', $paymentType->id);
            })
            ->where('status', 'successful')
            ->get();
    }

    /**
     * Progress of a payment type for this student.
     * Returns paid amount, full amount, fully_paid flag, and next installment info.
     */
    public function paymentTypeProgress($paymentType): array
    {
        $fullAmount = (float) $paymentType->amount;
        $payments = $this->paymentsForType($paymentType);
        $paid = $payments->sum('amount');

        // Full-payment rule: any successful payment explicitly marked as a full
        // payment (legacy 100% conversion or a "Pay Full" transaction) satisfies
        // the type. Fallback: installment IS NULL also counts as full (covers any
        // rows created before the flag was introduced).
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

        // Next installment (for split)
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
     * Payment types currently due for this student (active, matching programme + session, not fully paid).
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
            ->orderBy('sort_order')
            ->get();

        return $types->filter(function ($type) {
            return !$this->paymentTypeProgress($type)['fully_paid'];
        })->values();
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
