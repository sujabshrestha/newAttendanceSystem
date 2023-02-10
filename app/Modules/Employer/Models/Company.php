<?php

namespace Employer\Models;


use App\Models\CompanyGovernmentleave;
use App\Models\CompanySpecialleave;
use App\Models\User;
use Candidate\Models\Attendance;
use Candidate\Models\BusinessLeaveday;
use Candidate\Models\Candidate;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory, Sluggable;



    protected $fillable = [
        'name',
        'code',
        'employer_id',
        'address',
        'phone',
        'email',
        'working_days',
        'office_hour_start',
        'office_hour_end'
    ];

    protected $time =[
        'office_hour_start',
        'office_hour_end'
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }


    // Scopes
    public function scopeActive($q){
        return $q->where('status',"Active");
    }

    public function scopeInative($q){
        return $q->where('status',"Inactive");
    }

    // Relationships
    public function employer(){
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function candidates(){
        return $this->belongsToMany(User::class, 'company_candidates', 'company_id', 'candidate_id');
    }


    public function candidatesByCompanyID($companyid = null){
        // dd($this->id,$this->company_id);
        // dd($companyid);
        return $this->belongsToMany(User::class, 'company_candidates', 'company_id', 'candidate_id')->withPivot('code','office_hour_start',
        'office_hour_end', 'status','duty_time', 'salary_amount',
        'salary_type','overtime')->distinct();
    }

    public function users(){
        return $this->belongsToMany(User::class, 'company_candidates','company_id', 'user_id');
    }
   
    public function attendances(){
        return $this->hasMany(Attendance::class, 'company_id');
    }

    //company users

    public function govLeaves(){
        return $this->hasMany(CompanyGovernmentleave::class, 'company_id');
    }


    public function specialLeaves(){
        return $this->hasMany(CompanySpecialleave::class, 'company_id');
    }


    public function businessLeaves(){
        return $this->belongsToMany(BusinessLeaveday::class, 'company_businessleaves', 'company_id', 'business_leave_id');
    }

    public function leaveTypes(){
        return $this->hasMany(LeaveType::class, 'company_id');
    }



}
