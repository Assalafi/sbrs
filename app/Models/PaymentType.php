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
        'remita_split_enabled', 'remita_surcharge',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'split_percent' => 'decimal:2',
        'split_enabled' => 'boolean',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'installment_count' => 'integer',
        'remita_split_enabled' => 'boolean', 'remita_surcharge' => 'decimal:2',
    ];

    /**
     * Remita service type IDs pulled from the settings table.
     *
     * The value sent back to Remita remains the numeric service type ID, while
     * the label includes the configured description so administrators can tell
     * identical IDs used for different services apart in the dropdown.
     * Returns [value => label] for dropdowns.
     */
    public static function remitaServiceTypeOptions(): array
    {
        $settings = \App\Models\Setting::query()
            ->where('key', 'like', 'remita%service_type_id%')
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->get(['value', 'description']);

        $options = [];
        foreach ($settings->groupBy(fn ($setting) => trim((string) $setting->value)) as $value => $rows) {
            $descriptions = $rows->pluck('description')
                ->map(function ($description) {
                    $description = trim((string) $description);
                    // Settings store technical metadata such as "Service Type
                    // ID for IJMB Application Fee". Keep the useful service
                    // name in the dropdown while removing that boilerplate.
                    $description = preg_replace('/^service\s+type\s+id\s+for\s+/i', '', $description);
                    return trim((string) $description);
                })
                ->filter()
                ->unique()
                ->values()
                ->all();

            $options[$value] = $value . ($descriptions ? ' — ' . implode(' / ', $descriptions) : '');
        }

        ksort($options, SORT_NATURAL);
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
