<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory,SoftDeletes;

    protected $appends = ['status_name'];


    public function getStatusNameAttribute() 
    {
        return config('constants.status.'.$this->status);
    }

    public function repayments()
    {
        return $this->hasMany(\App\Models\LoanRepayment::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

}
