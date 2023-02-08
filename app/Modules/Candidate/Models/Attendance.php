<?php

namespace Candidate\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'leave_type_id',
        'start_time',
        'end_time',
        'employee_status',
        'company_id',
        'earning'
    ];


    protected $time = [
        'start_time',
        'end_time'
    ];

    public function candidate(){
        return $this->belongsTo(User::class,'candidate_id');
    }

}
