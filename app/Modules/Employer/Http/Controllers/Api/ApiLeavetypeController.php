<?php

namespace Employer\Http\Controllers\Api;

use App\GlobalServices\ResponseService;
use Illuminate\Http\Request;
use App\http\Controllers\Controller;
use Employer\Http\Resources\LeavetypeResource;
use Employer\Models\LeaveType;

class ApiLeavetypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     protected $response;
     public function __construct(ResponseService $response)
     {

         $this->response = $response;
     }


    public function index($company_id)
    {
        try{
            
            $leavetypes = LeaveType::latest()->get();
            // $leavetypes = LeaveType::where('company_id',$company_id)->latest()->get();
            $data = [
                'leavetypes' => LeavetypeResource::collection($leavetypes)
            ];

            return $this->response->responseSuccess($data, "Success", 200);


        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,$company_id)
    {
        try{
            $user = Auth()->user();
            $leaveType = new LeaveType();
            $leaveType->title = $request->title;
            $leaveType->status =$request->status;
            $leaveType->desc =$request->description;
            $leaveType->user_id = $user->id;
            $leaveType->company_id = $company_id;
            if($leaveType->save() == true){
                return $this->response->responseSuccessMsg("Successfully Created", 200);
            }
            return $this->response->responseError("Something Went Wrong While Saving. Please Try Again.");

        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$company_id ,$leave_type_id)
    {   
        try{
            $user_id = Auth()->id();
            $leaveType = LeaveType::where('company_id',$company_id)->where('id',$leave_type_id)->first();
            if($leaveType){
                $leaveType->title = $request->title;
                $leaveType->status =$request->status;
                $leaveType->desc =$request->description;
                $leaveType->user_id = $user_id;
                if($leaveType->update() == true){
                    return $this->response->responseSuccessMsg("Successfully Updated", 200);
                }
                return $this->response->responseError("Something Went Wrong While Updating. Please Try Again.");
            }
            return $this->response->responseError("Leave Type Not Found",404);
        
        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
       
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($company_id,$leave_type_id)
    {
        try{
            $leave = LeaveType::where('company_id',$company_id)->where('id',$leave_type_id)->first();
            if($leave){
                $leave->delete();
                return $this->response->responseSuccessMsg("Successfully Deleted", 200);
            }
            return $this->response->responseError("Leave Record Not Found",404);
        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }
}
