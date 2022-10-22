<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanRepaymentPaymentDetials extends Model
{
    use HasFactory,SoftDeletes;

    public function payment()
    {
        return $this->belongsTo(\App\Models\LoanRepaymentPayment::class, 'loan_repayment_payment_id', 'id');
    }
}
