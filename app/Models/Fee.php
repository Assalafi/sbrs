<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasUuid, Auditable;

    protected $fillable = [
        'academic_session_id',
        'fee_type',
        'programme_type',
        'programme_id',
        'amount',
        'description',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public static function getActiveFee(string $feeType, string $programmeType, ?string $sessionId = null, ?string $programmeId = null)
    {
        $sessionId = $sessionId ?? AcademicSession::current()?->id;

        $query = self::where('fee_type', $feeType)
            ->where('academic_session_id', $sessionId)
            ->where('is_active', true)
            ->where(function ($q) use ($programmeType) {
                $q->where('programme_type', $programmeType)
                  ->orWhere('programme_type', 'all');
            });

        // A programme-specific rule always wins over the general rule. This
        // lets Remedial Science and Remedial Arts have different amounts while
        // preserving all existing programme-wide fees as the fallback.
        if ($programmeId) {
            $specific = (clone $query)->where('programme_id', $programmeId)->first();
            if ($specific) return $specific;
        }

        return $query->whereNull('programme_id')
            ->orderByRaw("CASE WHEN programme_type = ? THEN 0 ELSE 1 END", [$programmeType])
            ->first();
    }
}
