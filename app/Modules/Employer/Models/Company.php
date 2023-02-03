<?php

namespace Employer\Models;

use App\Models\Candidate;
use App\Models\CompanyGovernmentleave;
use App\Models\CompanySpecialleave;
use App\Models\User;
use Candidate\Models\BusinessLeaveday;
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

    public function employer(){
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function candidates(){
        return $this->belongsTo(Candidate::class, 'company_candidates', 'company_id', 'candidate_id');
    }


    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }


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
