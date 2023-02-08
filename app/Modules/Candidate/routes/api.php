<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('candidateRoute.prefix.api'),
    'namespace' => config('candidateRoute.namespace.api'),
], function () {

    Route::post('register', 'ApiCandidateAuthController@register')->name('register');

    Route::post('verify-opt', 'ApiCandidateAuthController@verifyOtp')->name('verifyOtp');

    Route::post('password-submit', 'ApiCandidateAuthController@passwordSubmit')->name('passwordSubmit');

    Route::post('login', 'ApiCandidateAuthController@login')->name('login');


    Route::group([
        'middleware' => ['auth:api','candidateMiddleware']
    ], function(){

        Route::get('logout', 'ApiCandidateAuthController@logout')->name('logout');

        Route::post('profile-update', 'ApiCandidateAuthController@profileUpdate')->name('profileUpdate');


        Route::post('attendance-store/{company_id}', 'ApiAttendanceCandidateController@attendanceStore');

        Route::post('attendance-update/{company_id?}/{attendance_id?}', 'ApiAttendanceCandidateController@attendanceUpdate');

        Route::post('attendance-break-store/{attendance_id}', 'ApiAttendanceCandidateController@attendanceBreakStore');

        Route::post('attendance-break-update/{break_id}', 'ApiAttendanceCandidateController@attendanceBreakUpdate');

        // Route::group([
        //     'prefix' => '',
        //     'as' => 'candidate.'
        // ], function(){

        // });


        Route::get('get-companies', 'ApiCandidateCompanyController@getCompaniesByCandidateID');


        Route::get('all-leaves/{companyid}','ApiCandidateLeaveController@allCandidateLeave');

        Route::post('store-leave/{companyid}','ApiCandidateLeaveController@storeCandidateLeave');

        Route::get('update-leave/{companyid}/{leave_id}','ApiCandidateLeaveController@updateCandidateLeave');

        Route::get('delete-leave/{companyid}/{leave_id}','ApiCandidateLeaveController@deleteCandidateLeave');

        Route::get('leave-types','ApiCandidateLeaveController@getLeaveTypes');


        Route::group([
            'prefix' => '/invitation',

        ], function(){
            Route::get('all','ApiCandidateInvitationController@allCandidateInvitations');

            Route::post('invitation-update/{invitation_id}','ApiCandidateInvitationController@updateCandidateInvitation');
        });



        Route::group([
            'prefix' => 'report',
        ], function(){
            Route::get('weekly/{company_id}','ApiCandidateReportController@weeklyReport');

            Route::get('monthly/{company_id}','ApiCandidateReportController@monthlyReport');

            Route::get('yearly/{company_id}/{year}','ApiCandidateReportController@yearlyReport');

        });



        Route::post('change-phonenumber', 'ApiCandidateAuthController@changePhone')->name('changePhone');






    });


});
