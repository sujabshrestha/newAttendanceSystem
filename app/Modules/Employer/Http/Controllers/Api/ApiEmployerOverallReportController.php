<?php

namespace Employer\Http\Controllers\Api;

use App\GlobalServices\ResponseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Candidate\Models\CompanyCandidate;

class ApiEmployerOverallReportController extends Controller
{

    protected $response;
    public function __construct(ResponseService $response)
    {
        $this->response = $response;
    }


    public function dailyReport($id){
        try{

            $totalattendee = CompanyCandidate::where('company_id', $id)->count();
            $absentCount = CompanyCandidate::where('company_id', $id)->with(['candidate'
            => function($q){
                $q->whereDoesntHave('attendances');
            }
            ])->count();


            $presentCount = CompanyCandidate::where('company_id', $id)
            ->whereHas('candidate'
            , function($q){
                $q->whereHas('attendances', function($q){
                    $q->whereDate('created_at', today())
                    ->where('employee_status', 'Present');
                });
            }
            )->count();
            $lateCount = CompanyCandidate::where('company_id', $id)
            ->whereHas('candidate'
            , function($q){
                $q->whereHas('attendances', function($q){
                    $q->whereDate('created_at', today())
                    ->where('employee_status', 'Late');
                });
            }
            )->count();

            $LeaveCount = CompanyCandidate::where('company_id', $id)
            ->whereHas('candidate'
            , function($q){
                $q->whereHas('attendances', function($q){
                    $q->whereDate('created_at', today())
                    ->where('employee_status', 'Leave');
                });
            }
            )->count();

            $punchOutCount = CompanyCandidate::where('company_id', $id)
            ->whereHas('candidate'
            , function($q){
                $q->whereHas('attendances', function($q){
                    $q->whereDate('created_at', today())
                    ->where('employee_status', 'punchOut');
                });
            }
            )->count();

<<<<<<< HEAD
            $totalPresentToday = $presentCount ?? 0 +  $lateCount ?? 0;
=======
>>>>>>> e67b342b62d3290c556b5bccb97256910ff64776
            $data = [
                'total_attendee' =>   $totalattendee ?? 0,
                'present' => $presentCount ?? 0,
                'absent' => $absentCount ?? 0,
                'late' => $lateCount ?? 0,
                'punch_out' => $punchOutCount ?? 0,
<<<<<<< HEAD
                'leave' => $LeaveCount ?? 0,
                'percentage' => cal_percentage($totalPresentToday, $totalattendee) ?? 0
=======
                'leave' => $LeaveCount ?? 0
>>>>>>> e67b342b62d3290c556b5bccb97256910ff64776
            ];

            return $this->response->responseSuccess($data, "Success", 200);


        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }



    public function weeklyReport($id){
        try{

            $totalattendee = CompanyCandidate::where('company_id', $id)->count();
            $absentCount = CompanyCandidate::where('company_id', $id)->with(['candidate'
            => function($q){
                $q->whereDoesntHave('weeklyattendances');
            }
            ])->count();


            $presentCount = CompanyCandidate::where('company_id', $id)
            ->whereHas('candidate'
            , function($q){
                $q->whereHas('attendances', function($q){
                    $q->whereDate('created_at', today())
                    ->where('employee_status', 'Present');
                });
            }
            )->count();
            $lateCount = CompanyCandidate::where('company_id', $id)
            ->whereHas('candidate'
            , function($q){
                $q->whereHas('attendances', function($q){
                    $q->whereDate('created_at', today())
                    ->where('employee_status', 'Late');
                });
            }
            )->count();

            $LeaveCount = CompanyCandidate::where('company_id', $id)
            ->whereHas('candidate'
            , function($q){
                $q->whereHas('attendances', function($q){
                    $q->whereDate('created_at', today())
                    ->where('employee_status', 'Leave');
                });
            }
            )->count();

            $punchOutCount = CompanyCandidate::where('company_id', $id)
            ->whereHas('candidate'
            , function($q){
                $q->whereHas('attendances', function($q){
                    $q->whereDate('created_at', today())
                    ->where('employee_status', 'punchOut');
                });
            }
            )->count();
<<<<<<< HEAD
            $totalPresentToday = $presentCount ?? 0 +  $lateCount ?? 0;
=======

>>>>>>> e67b342b62d3290c556b5bccb97256910ff64776
            $data = [
                'total_attendee' =>   $totalattendee ?? 0,
                'present' => $presentCount ?? 0,
                'absent' => $absentCount ?? 0,
                'late' => $lateCount ?? 0,
                'punch_out' => $punchOutCount ?? 0,
<<<<<<< HEAD
                'leave' => $LeaveCount ?? 0,
                'percentage' => cal_percentage($totalPresentToday, $totalattendee)
=======
                'leave' => $LeaveCount ?? 0
>>>>>>> e67b342b62d3290c556b5bccb97256910ff64776
            ];

            return $this->response->responseSuccess($data, "Success", 200);


        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }




    public function monthlyReport($id){
        try{



            $totalattendee = CompanyCandidate::where('company_id', $id)->count();
            $absentCount = CompanyCandidate::where('company_id', $id)->with(['candidate'
            => function($q){
                $q->whereDoesntHave('monthlyattendances');
            }
            ])->count();




            $presentCount = CompanyCandidate::where('company_id', $id)
            ->whereHas('candidate'
            , function($q){
                $q->whereHas('attendances', function($q){
                    $q->whereMonth('created_at', today()->format('m'))
                    ->where('employee_status', 'Present');
                });
            }
            )->count();

            $lateCount = CompanyCandidate::where('company_id', $id)
            ->whereHas('candidate'
            , function($q){
                $q->whereHas('attendances', function($q){
                    $q->whereMonth('created_at', today()->format('m'))
                    ->where('employee_status', 'Late');
                });
            }
            )->count();

            $LeaveCount = CompanyCandidate::where('company_id', $id)
            ->whereHas('candidate'
            , function($q){
                $q->whereHas('attendances', function($q){
                    $q->whereMonth('created_at', today()->format('m'))
                    ->where('employee_status', 'Leave');
                });
            }
            )->count();

            $punchOutCount = CompanyCandidate::where('company_id', $id)
            ->whereHas('candidate'
            , function($q){
                $q->whereHas('attendances', function($q){
                    $q->whereMonth('created_at', today()->format('m'))
                    ->where('employee_status', 'punchOut');
                });
            }
            )->count();
<<<<<<< HEAD
            $totalPresentToday = $presentCount ?? 0 +  $lateCount ?? 0;
=======

>>>>>>> e67b342b62d3290c556b5bccb97256910ff64776
            $data = [
                'total_attendee' =>   $totalattendee ?? 0,
                'present' => $presentCount ?? 0,
                'absent' => $absentCount ?? 0,
                'late' => $lateCount ?? 0,
                'punch_out' => $punchOutCount ?? 0,
<<<<<<< HEAD
                'leave' => $LeaveCount ?? 0,
                'percentage' => cal_percentage($totalPresentToday, $totalattendee)
=======
                'leave' => $LeaveCount ?? 0
>>>>>>> e67b342b62d3290c556b5bccb97256910ff64776
            ];

            return $this->response->responseSuccess($data, "Success", 200);


        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }




    public function yearlyReport($id){
        try{



            $totalattendee = CompanyCandidate::where('company_id', $id)->count();
            $absentCount = CompanyCandidate::where('company_id', $id)->with(['candidate'
            => function($q){
                $q->whereDoesntHave('yearlyattendances');
            }
            ])->count();




            $presentCount = CompanyCandidate::where('company_id', $id)
            ->whereHas('candidate'
            , function($q){
                $q->whereHas('attendances', function($q){
                    $q->whereYear('created_at', date('Y'))
                    ->where('employee_status', 'Present');
                });
            }
            )->count();

            $lateCount = CompanyCandidate::where('company_id', $id)
            ->whereHas('candidate'
            , function($q){
                $q->whereHas('attendances', function($q){
                    $q->whereYear('created_at', date('Y'))
                    ->where('employee_status', 'Late');
                });
            }
            )->count();

            $LeaveCount = CompanyCandidate::where('company_id', $id)
            ->whereHas('candidate'
            , function($q){
                $q->whereHas('attendances', function($q){
                    $q->whereYear('created_at', date('Y'))
                    ->where('employee_status', 'Leave');
                });
            }
            )->count();

            $punchOutCount = CompanyCandidate::where('company_id', $id)
            ->whereHas('candidate'
            , function($q){
                $q->whereHas('attendances', function($q){
                    $q->whereYear('created_at', date('Y'))
                    ->where('employee_status', 'punchOut');
                });
            }
            )->count();
<<<<<<< HEAD
            $totalPresentToday = $presentCount ?? 0 +  $lateCount ?? 0;
=======

>>>>>>> e67b342b62d3290c556b5bccb97256910ff64776
            $data = [
                'total_attendee' =>   $totalattendee ?? 0,
                'present' => $presentCount ?? 0,
                'absent' => $absentCount ?? 0,
                'late' => $lateCount ?? 0,
                'punch_out' => $punchOutCount ?? 0,
<<<<<<< HEAD
                'leave' => $LeaveCount ?? 0,
                'percentage' => cal_percentage($totalPresentToday, $totalattendee)
=======
                'leave' => $LeaveCount ?? 0
>>>>>>> e67b342b62d3290c556b5bccb97256910ff64776
            ];

            return $this->response->responseSuccess($data, "Success", 200);


        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }






}
