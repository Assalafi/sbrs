<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasUuid;

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCESSFUL = 'successful';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'payable_type',
        'payable_id',
        'payment_type',
        'academic_session_id',
        'fee_id',
        'amount',
        'currency',
        'rrr',
        'order_id',
        'description',
        'status',
        'payment_method',
        'gateway_response',
        'paid_at',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function payable()
    {
        return $this->morphTo();
    }

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }

    public function hasRrr(): bool
    {
        return !empty($this->rrr);
    }

    public function markAsSuccessful(array $gatewayResponse = [], string $paymentMethod = null): bool
    {
        return $this->update([
            'status' => self::STATUS_SUCCESSFUL,
            'paid_at' => now(),
            'gateway_response' => $gatewayResponse,
            'payment_method' => $paymentMethod,
        ]);
    }

    public function markAsFailed(array $gatewayResponse = []): bool
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
            'gateway_response' => $gatewayResponse,
        ]);
    }
}
