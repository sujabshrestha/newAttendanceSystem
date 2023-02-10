<?php

namespace SuperAdmin\Http\Controllers\Backend;

use App\GlobalServices\ResponseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Employer\Models\Company;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class SuperAdminEmployerController extends Controller
{
    public $response;

    public function __construct(ResponseService $response)
    {
        $this->response = $response;
    }
   
    public function index(){
        try{
            return view('SuperAdmin::backend.employer.index');
        }catch(Exception $e){
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    // public function create(){
    //     try{
    //         return view('Receptionist::.client.create');
    //     }catch(Exception $e){
    //         Toastr::error($e->getMessage());
    //         return redirect()->back();
    //     }
    // }


    // public function store(Request $request ){
    //     try {
    //         // dd($request->all());
    //         $client = $this->client->store($request);
    //         if($client == true) {
    //             Toastr::success('Successfully Created.');
    //             return redirect()->route('receptionist.client.create');
    //         }
    //         Toastr::error("Something Went Wrong");
    //         return redirect()->back();
    //     } catch (Exception $e) {
    //         Toastr::error($e->getMessage());
    //         return redirect()->back();
    //     }
    // }

    // public function edit($id){
    //     try{
    //         $client = $this->client->getByID($id);
    //         return view('Receptionist::client.edit',compact('client'));
    //     }catch(Exception $e){
    //         Toastr::error($e->getMessage());
    //         return redirect()->back();
    //     }
    // }


    public function show($id){
        try{
            $employer = User::employers()->where('id', $id)->with('employerCompany')->first();
            // dd($employer);
            return view('SuperAdmin::backend.employer.show',compact('employer'));
        }catch(Exception $e){
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    // public function update(Request $request, $id){
    //     // try {
    //         // dd($request->all());
    //         $user = $this->client->update($request, $id);
    //         if ($user == true) {
    //             Toastr::success('Successfully Updated.');
    //             return redirect()->route('receptionist.client.index');
    //         }
    //         Toastr::error("Something Went Wrong");
    //         return redirect()->back();
    //     // } catch (Exception $e) {
    //     //     Toastr::error($e->getMessage());
    //     //     return redirect()->back();
    //     // }
    // }

    public function destroy($id){
        try{
            dd($id);
            $employer = User::employers()->where('id', $id)->first();
            if($employer == true){
                Toastr::success('Successfully Deleted.');
                return redirect()->back();
            }
            Toastr::error("Something Went Wrong");
            return redirect()->back();
        }catch(Exception $e){
             Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    // public function trashedIndex(){
    //     return view('Receptionist::user.trashedIndex');
    // }

    // public function trashedDestroy($id){
    //     try{
    //         $user=$this->client->trashedDestroy($id);
    //         if($user == true){
    //             return $this->response->responseSuccessMsg('Deleted Permanently');
    //         }
    //     }catch(Exception $e){
    //          Toastr::error($e->getMessage());
    //         return redirect()->back();
    //     }
    // }



    public function getEmployerData(Request $request){
        try {
            if ($request->ajax()) {
                $data = User::employers()->with('employerCompany');
                // dd($data);
                return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('company',function($row){
                    return $row->employerCompany ? $row->employerCompany->count() : 'No Company';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '
                    <a class="btn btn-primary btn-sm" href="'. route('backend.employer.show',$row->id) .'">View</a>
                    ';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
            }
        } catch (Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }

    // public function getTrashedUserData(Request $request){
    //     try {
    //         if ($request->ajax()) {
    //             $data = Client::onlyTrashed()->get();
    //             return Datatables::of($data)
    //             ->addColumn('action', function ($row) {
    //                 $actionBtn = '<a href="javascript:void(0)" id="'. route('backend.user.trashedRecover',$row->id) .'" data-id=' . $row->id . ' class="restore btn btn-success btn-sm">Restore</a>
    //                             <a href="javascript:void(0)" id="'. route('backend.user.trashedDestroy',$row->id) .'" data-id=' . $row->id . ' class="permanentDelete btn btn-danger btn-sm">Permanent Delete</a>';
    //                 return $actionBtn;
    //             })
    //             ->rawColumns(['action'])
    //             ->make(true);
    //             }
    //     } catch (Exception $e) {
    //         return $this->response->responseError($e->getMessage());
    //     }
    // }

    // public function changeClientStatus(Request $request,$id){
    //     // dd($request->all());
    //     $client= Company::where('id',$id)->first();
    //     if($client){
    //         $client->status = $request->status;
    //         $client->update();
    //         return $this->response->responseSuccessMsg('Successfully Updated',200);
    //     }
    // }
}
