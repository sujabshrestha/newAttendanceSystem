<?php

namespace Employer\Http\Controllers\Api;

use App\GlobalServices\ResponseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Employer\Http\Requests\CompanyUpdateRequest;
use Employer\Http\Resources\CompanyResource;
use Employer\Models\Company;
use Employer\Repositories\company\CompanyInterface;

class ApiCompanyController extends Controller
{

    protected $response, $company;
    public function __construct(ResponseService $response, CompanyInterface $company)
    {

        $this->response = $response;
        $this->company = $company;
    }


    public function index(){
        try {


            $companies = $this->company->getAllCompanies();

            if($companies){

                $data = [
                    'companies' => CompanyResource::collection($companies)
                ];
                return $this->response->responseSuccess($data, "Success", 200);
            }
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }




    public function getCompanyByID($id){
        try {


            $company = Company::where('id', $id)->where('employer_id', auth()->user()->id)->first();

            if($company){

                $data = [
                    'company' =>  new CompanyResource($company)
                ];
                return $this->response->responseSuccess($data, "Success", 200);
            }
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }


    public function getCompaniesByEmployer(){
        try {

            $companies = $this->company->getCompaniesByEmployerId();

            if($companies){

                $data = [
                    'companies' => CompanyResource::collection($companies)
                ];
                return $this->response->responseSuccess($data, "Success", 200);
            }
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }





    public function store(Request $request)
    {
        // try {

            $companystore = $this->company->store($request);
            if($companystore){
                return $this->response->responseSuccessMsg("Successfully stored", 200);
            }
        // } catch (\Exception $e) {
        //     return $this->response->responseError($e->getMessage());
        // }
    }



    public function update(CompanyUpdateRequest $request, $id)
    {
        try {

            $companystore = $this->company->update($request, $id);
            if($companystore){
                return $this->response->responseSuccessMsg("Successfully updated", 200);
            }
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }



    public function destroy( $id)
    {
        try {
            $company= $this->company->getCompanyByIdWithCandidates($id);
            if($company){
                if($company->candidates->isNotEmpty()){
                    $company->candidates->delete();
                    return $this->response->responseSuccessMsg("Successfully updated", 200);

                }
                return $this->response->responseError("Company not found", 400);

            }
            return $this->response->responseError("Company not found", 400);

        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }
}
