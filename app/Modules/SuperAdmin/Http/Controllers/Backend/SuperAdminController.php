<?php

namespace SuperAdmin\Http\Controllers\Backend;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Employer\Models\Company;
use Illuminate\Support\Facades\Auth;

class SuperAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        try{

            $companies = Company::query();
            $latestCompanies = $companies->latest()->limit(10)->get();
            $companiesCount = $companies->count();

            // $latestCompanies =  Company::latest()->limit(10)->get();
            // $companiesCount =  Company::count();

            $candidatesCount= User::candidateCheck()->count();
            $employersCount= User::employers()->count();
            return view('SuperAdmin::backend.dashboard',compact('companiesCount','latestCompanies','employersCount','candidatesCount'));
        }catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function profile(){
        try{
            $user = Auth::user();
            return view('SuperAdmin::backend.profile.userprofile',compact('user'));
        }catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    

   
   
}
