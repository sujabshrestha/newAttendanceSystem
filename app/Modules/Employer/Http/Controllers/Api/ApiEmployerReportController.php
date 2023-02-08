<?php

namespace Employer\Http\Controllers\Api;

use App\GlobalServices\ResponseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Candidate\Http\Resources\CandidateResource;
use Candidate\Models\Attendance;
use Employer\Http\Resources\CurrentDayAttendanceReportResource;
use Employer\Models\Company;
use Illuminate\Support\Facades\Auth;

class ApiEmployerReportController extends Controller
{



    protected $response;
    public function __construct(ResponseService $response)
    {

        $this->response = $response;
    }

    public function currentDayReport($companyid){
        try{


            $data = [
                'employee' => new CurrentDayAttendanceReportResource('fdkjashfkj')
            ];
            return $this->response->responseSuccess($data, "Success", 200);
        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }



}
