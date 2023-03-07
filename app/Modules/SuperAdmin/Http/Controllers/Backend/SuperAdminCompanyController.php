<?php

namespace SuperAdmin\Http\Controllers\Backend;

use App\GlobalServices\ResponseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Employer\Models\Company;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class SuperAdminCompanyController extends Controller
{
    public $response;

    public function __construct(ResponseService $response)
    {
        $this->response = $response;
    }
   
    public function index(){
        try{
            return view('SuperAdmin::backend.company.index');
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


    public function show($slug){
        try{
            $company = Company::where('slug', $slug)->with('employer','candidates')->first();
            // dd($company);
            return view('SuperAdmin::backend.company.show',compact('company'));
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
            $company = Company::where('id', $id)->first();
            if($company == true){
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



    public function getCompanyData(Request $request){
        try {
            if ($request->ajax()) {
                $data = Company::query()->with('employer','candidates');
                // dd($data);
                return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('employer',function($row){
                    return $row->employer ? $row->employer->name : 'No Employer';
                })
                ->editColumn('candidate_count',function($row){
                    return $row->candidates->count();
                })
                ->editColumn('status',function($row){
                    $main = '<select name="status" class="form-control clientStatus" data-id='.$row->id.'>';
                    $mainlast = '</select>';
                    $activeSelected = '<option  value="Active" selected>Active</option>
                                        <option  value="Inactive">Inactive</option>';
                    $inactiveSelected = '<option  value="Inactive">Active</option>
                                        <option  value="Inactive" selected>Inactive</option>';

                    if($row->status == "Active"){
                        return $main.$activeSelected.$mainlast;
                    }else{
                        return $main.$inactiveSelected.$mainlast;
                    }
                })


                ->addColumn('action', function ($row) {
                    $actionBtn = '
                    <a class="btn btn-primary btn-sm" href="'. route('backend.company.show',$row->slug) .'">View</a>
                   ';
                    return $actionBtn;
                })
                ->rawColumns(['action','status'])
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
