<?php

namespace SuperAdmin\Http\Controllers\Backend;

use App\GlobalServices\ResponseService;
use Employer\Models\LeaveType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class SuperAdminLeaveTypeController extends Controller
{
    public $response;
    
    public function __construct(ResponseService $response)
    {
        $this->response = $response;
    }

    public function index(){
        return view('SuperAdmin::backend.leaveType.index');
    }

    public function getleaveTypeData(Request $request){
        try {
            if($request->ajax()) {
                $data = LeaveType::query();
                return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action',function($row){
                    $actionBtn = '<a href="javascript:void(0)" id="'. route('backend.leave.type.edit',$row->slug) .'" data-id=' . $row->slug . ' class="edit btn btn-info btn-sm" title="Edit"><i
                                class="far fa-edit"></i></a>
                                <a href="javascript:void(0)" id="'. route('backend.leave.type.destroy',$row->slug) .'" data-id='.$row->slug.' class="delete btn btn-danger btn-sm" title="Delete"><i
                                class="far fa-trash-alt"></i></a>
                              ';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
            }
        }catch (Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }

    public function store(Request $request ){
        try {
            $leaveType = new LeaveType();
            $leaveType->title = $request->title;
            $leaveType->status = $request->status;
            $leaveType->desc = $request->remarks;
            if($leaveType->save() == true){
                return $this->response->responseSuccessMsg('Successfully Stored!!');
            }
        } catch(Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }

    public function edit($slug){
        try{
            $leaveType = LeaveType::where('slug',$slug)->first();
            if($leaveType){
                return view('SuperAdmin::backend.leaveType.edit',compact('leaveType'));
            }
        }catch(Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }

    public function update(Request $request,$slug ){
        try {
            $leaveType = LeaveType::where('slug',$slug)->first();
            if($leaveType){
                $leaveType->title = $request->title;
                $leaveType->status = $request->status;
                $leaveType->desc = $request->remarks;
                if ($leaveType->update() == true) {
                    return $this->response->responseSuccessMsg('Successfully Updated!!');
                }
            }
          
        } catch (Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }

    public function destroy($slug){
        try{
            $leaveType = LeaveType::where('slug',$slug)->first();
            if($leaveType){
                $leaveTypeDelete=$leaveType->delete();
                if($leaveTypeDelete == true){
                    return $this->response->responseSuccessMsg('Successfully Deleted');
                }
            }
           
            return $this->response->responseError('Can not be Deleted');
        }catch(Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }

    // public function restore($id){
    //     try{
    //         $leaveTypeRestore=$this->leaveType->restore( $id);
    //         if($leaveTypeRestore == true){
    //             return $this->response->responseSuccessMsg('Successfully Restore');
    //         }
    //     }catch(Exception $e){
    //         return $this->response->responseError($e->getMessage());
    //     }
    // }

    // public function permanentDelete($id){
    //     try{
    //         $leaveTypeDelete=$this->leaveType->permanentDelete($id);
    //         if($leaveTypeDelete == true){
    //             return $this->response->responseSuccessMsg('Successfully Delete Permanently');
    //         }
    //     }catch(Exception $e){
    //         return $this->response->responseError($e->getMessage());
    //     }
    // }


    public function trashedIndex(){
        return view('SuperAdmin::backend.leaveType..indexTrash');
    }

    public function getTrashedleaveTypeData(Request $request){
        try {
            if ($request->ajax()){
                $data = leaveType::onlyTrashed()->latest();
                return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action',function($row){
                    $actionBtn = '<a href="javascript:void(0)"  id="'. route('backend.global.option.leaveType.restore',$row->id) .'" data-id=' . $row->id . '
                        class="restore btn btn-success btn-sm">Restore</a>
                    <a href="javascript:void(0)"  id="'. route('backend.global.option.leaveType.permanentDelete',$row->id) .'"  data-id=' . $row->id . ' class="permanentDelete btn btn-danger btn-sm">Permanent
                        Delete</a>';
                return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
            }
        } catch (Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }
}
