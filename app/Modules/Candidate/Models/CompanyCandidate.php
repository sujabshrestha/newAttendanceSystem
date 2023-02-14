<?php

namespace Candidate\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyCandidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'candidate_id',
        'verified_status',
        'status',
        'office_hour_start',
        'office_hour_end',
        'code',
        'salary_amount',
        'salary_type',
        'overtime',

    ];


    protected $time = [
        'office_hour_start',
        'office_hour_end'
    ];



    public function candidate(){
        return $this->belongsTo(User::class, 'candidate_id');
    }


}
