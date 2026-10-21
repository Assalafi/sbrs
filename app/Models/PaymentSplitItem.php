<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSplitItem extends Model
{
    protected $fillable = ['payment_id','line_item_id','beneficiary_name','beneficiary_account','bank_code','beneficiary_amount','is_primary'];
    protected $casts = ['beneficiary_amount'=>'decimal:2','is_primary'=>'boolean'];
    public function payment() { return $this->belongsTo(Payment::class); }
}
