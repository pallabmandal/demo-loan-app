<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanRepayment extends Model
{
    use HasFactory,SoftDeletes;

    protected $appends = ['status_name'];


    public function getStatusNameAttribute() 
    {
        return config('constants.status.'.$this->status);
    }

    public function repayment_payments()
    {
        return $this->hasMany(\App\Models\LoanRepaymentPaymentDetials::class);
    }
}
