<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('employerRoute.prefix.api'),

    'namespace' => config('employerRoute.namespace.api'),
], function () {

    //employer opt verification and first register

    Route::post('register', 'ApiEmployerAuthController@register')->name('register');

    Route::post('verify-opt', 'ApiEmployerAuthController@verifyOtp')->name('verifyOtp');

    Route::post('password-submit', 'ApiEmployerAuthController@passwordSubmit')->name('passwordSubmit');

    Route::post('login', 'ApiEmployerAuthController@login')->name('login');

    Route::group([
        'middleware' => ['auth:api', 'employerMiddleware']
    ], function () {

        Route::get('logout', 'ApiEmployerAuthController@logout')->name('logout');

        Route::post('profile-update', 'ApiEmployerAuthController@profileUpdate')->name('profileUpdate');

        Route::group([
            'prefix' => 'company',
            'as' => 'company.'
        ], function () {
            Route::get('/all',  'ApiCompanyController@index');

            Route::post('/store',  'ApiCompanyController@store');

            Route::post('/update/{id}',  'ApiCompanyController@update');

            Route::post('/destroy/{id}',  'ApiCompanyController@destroy');

            Route::get('/employercompanies', 'ApiCompanyController@getCompaniesByEmployer' );

            Route::get('/{id}',  'ApiCompanyController@getCompanyByID');
        });


        Route::group([
            'prefix' => 'candidate'
        ], function () {
            Route::post('store/{companyid}', 'ApiCandidateController@store');

            Route::get('get-candidates/{companyid}', 'ApiCandidateController@getCandidatesByCompany');

            Route::get('get-companies/{candidateid}', 'ApiCandidateController@getCompaniesByCandidateID');
        });

        Route::group([
            'prefix' => 'leave-type',

        ], function () {
            Route::get('/all', 'ApiLeavetypeController@index');

            Route::post('/store', 'ApiLeavetypeController@store');

            Route::post('/update/{leave_type_id}', 'ApiLeavetypeController@update');

            Route::post('/destroy/{leave_type_id}', 'ApiLeavetypeController@destroy');
        });

        Route::group([
            'prefix' => '{company_id}/invitation',

        ], function () {
            Route::get('/', 'ApiInvitationController@index');

            Route::post('/store', 'ApiInvitationController@store');

            Route::post('/update/{leave_type_id}', 'ApiInvitationController@update');

            Route::post('/destroy/{leave_type_id}', 'ApiInvitationController@destroy');

            Route::get('/all-candidates', 'ApiInvitationController@allCandidates');
        });

        Route::group([
            'prefix' => '{company_id}/attendance',

        ], function () {
            Route::get('/currentDayAttendace', 'ApiAttendanceController@currentDayAttendance');

            Route::post('/store', 'ApiAttendanceController@store');

            Route::post('/update/{leave_type_id}', 'ApiAttendanceController@update');

            Route::post('/destroy/{leave_type_id}', 'ApiAttendanceController@destroy');

            Route::get('/all-candidates', 'ApiAttendanceController@allCandidates');
        });




        //company candidate leaves

        Route::group([
            'prefix' => 'candidateLeave'
        ], function(){
            Route::get('all/{companyid}','ApiEmployerCandidateLeaveController@all');

            Route::get('detail/{id}','ApiEmployerCandidateLeaveController@leaveDetail');

            Route::get('approve/{id}','ApiEmployerCandidateLeaveController@leaveApproval');
        });


        Route::group([
            'prefix' => 'report'
        ], function(){
            Route::get('today/{companyid}','ApiEmployerReportController@currentDayReport');

            Route::get('weekly-report/{company_id}/{candidate_id}','ApiEmployerReportController@weeklyReport');

            Route::get('monthly-report/{company_id}/{candidate_id}  ','ApiEmployerReportController@monthlyReport');

            Route::get('yearly-report/{company_id}/{candidate_id}','ApiEmployerReportController@yearlyReport');

            Route::post('payment-submit/{company_id}/{candidate_id}','ApiEmployerReportController@paymentSubmit');
        });


        Route::post('change-phonenumber', 'ApiEmployerAuthController@changePhone')->name('changePhone');


    });
});
