<?php

namespace Employer\Http\Controllers\Api;

use App\GlobalServices\ResponseService;
use Illuminate\Http\Request;
use App\http\Controllers\Controller;
use Employer\Http\Resources\LeavetypeResource;
use Employer\Http\Resources\PackageResource;
use Employer\Models\LeaveType;
use SuperAdmin\Models\Package;

class ApiPackageController extends Controller
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

    public function index()
    {
        try{
            $packages = Package::active()->latest()->get();
            $data = [
                'packages' => PackageResource::collection($packages)
            ];
            return $this->response->responseSuccess($data, "Success", 200);
        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }

   
}
