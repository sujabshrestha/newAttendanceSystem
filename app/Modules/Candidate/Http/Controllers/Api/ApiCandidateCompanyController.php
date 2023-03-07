<?php

namespace Candidate\Http\Controllers\Api;

use App\GlobalServices\ResponseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Employer\Http\Resources\CompanyResource;

class ApiCandidateCompanyController extends Controller
{

    protected $response;
    public function __construct(ResponseService $response)
    {

        $this->response = $response;
    }

    public function getCompaniesByCandidateID()
    {
        try {
            $user = auth()->user();
            $user = User::where('id', $user->id)->candidateCheck()->with(['companiesByCandidateID'])->first();


            if ($user) {
                $companies = CompanyResource::collection($user->companiesByCandidateID);
            }
            $data = [
                'companies' =>$companies ?? []
            ];

            return $this->response->responseSuccess($data, "Success", 200);

        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }
}
