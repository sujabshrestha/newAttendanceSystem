<?php

namespace Employer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable =[
        'status',
        'paid_amount',
        'payment_date',
        'payment_for_month',
        'company_id',
        'candidate_id',
        'employer_id',
    ];

    protected $dates = ['payment_date','payment_for_month',];
}
