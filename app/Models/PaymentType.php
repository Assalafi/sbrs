<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    use HasUuid, Auditable;

    protected $fillable = [
        'code',
        'name',
        'description',
        'programme_type',
        'academic_session_id',
        'payer_type',
        'amount',
        'split_enabled',
        'split_percent',
        'installment_count',
        'is_required',
        'is_active',
        'sort_order',
        'remita_service_type_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'split_percent' => 'decimal:2',
        'split_enabled' => 'boolean',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'installment_count' => 'integer',
    ];

    /**
     * Unique Remita service type IDs pulled from the settings table.
     * Returns [value => label] for dropdowns.
     */
    public static function remitaServiceTypeOptions(): array
    {
        $values = \App\Models\Setting::where('key', 'like', 'remita%service_type_id%')
            ->pluck('value')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $options = [];
        foreach ($values as $value) {
            $options[$value] = $value;
        }
        return $options;
    }

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Map this (possibly admin-created) payment type code to the legacy
     * payments.payment_type enum category.
     *
     * Codes such as "registration_fee" or "late_examination" are valid in
     * payment_types.code (free string) but are NOT valid values for the
     * legacy payments.payment_type enum, so we store the closest category.
     */
    public function legacyType(): string
    {
        $code = strtolower($this->code);

        foreach (['application', 'admission', 'registration', 'examination', 'hostel'] as $type) {
            if (str_contains($code, $type)) {
                return $type;
            }
        }

        return 'other';
    }

    public function getFirstInstallmentAmountAttribute(): ?float
    {
        if (!$this->split_enabled || $this->installment_count <= 1) {
            return null;
        }
        $percent = $this->split_percent ?: (100 / $this->installment_count);
        return round((float) $this->amount * ($percent / 100), 2);
    }
}
