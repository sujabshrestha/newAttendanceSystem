<?php

use Candidate\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     $data = Attendance::whereYear('created_at',2023)
//                 ->select(DB::raw("(sum(earning)) as earning"), DB::raw("GROUP_CONCAT(DISTINCT candidate_id) as candidate"),
//                     DB::raw("GROUP_CONCAT(DISTINCT company_id) as company"),DB::raw("DATE_FORMAT(created_at,'%M') as month"))
//                 ->groupBy(DB::raw("DATE_FORMAT(created_at,'%M')"))
//                 ->get();
    
// dd($data);
// });
