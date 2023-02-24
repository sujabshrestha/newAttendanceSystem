<?php

namespace Candidate\Models;

use App\Models\Invitation;
use App\Models\User;
use Carbon\Carbon;
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
        'designation',
        'joining_date',
        'duty_time',
        'allow_late_attendance',
        'invitation_id'

    ];


    protected $time = [
        'office_hour_start',
        'office_hour_end'
    ];



    public function candidate(){
        return $this->belongsTo(User::class, 'candidate_id');
    }



    public function attendaces(){
        return $this->hasMany(Attendance::class, 'candidate_id', 'candidate_id');
    }


    public function companyCandidateAttendaces(){
        return $this->belongsTo(Attendance::class, 'candidate_id', 'candidate_id')->where('created_at', Carbon::parse(today()));
    }



    public function activecompanyCandidateAttendaces(){
        return $this->belongsTo(Attendance::class, 'candidate_id', 'candidate_id')
        ->where('created_at', Carbon::parse(today()))->whereNull('end_time');
    }
    
    public function invitation(){
        return $this->belongsTo(Invitation::class, 'invitation_id');
    }


}
