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

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public static function getActiveFee(string $feeType, string $programmeType, ?string $sessionId = null)
    {
        $sessionId = $sessionId ?? AcademicSession::current()?->id;

        return self::where('fee_type', $feeType)
            ->where('academic_session_id', $sessionId)
            ->where('is_active', true)
            ->where(function ($q) use ($programmeType) {
                $q->where('programme_type', $programmeType)
                  ->orWhere('programme_type', 'all');
            })
            ->first();
    }
}
