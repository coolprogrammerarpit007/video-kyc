<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\KycSessionController;
use App\Http\Controllers\Api\V1\VerifierKycSessionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



//  *************** Logout API ***********************

Route::post('/auth/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');

// ***************************************************
Route::prefix('v1')->group(function(){
    Route::post('/auth/register',[AuthController::class,'register']);
    Route::post('/auth/login',[AuthController::class,'login']);


    // ***************** Testing Middleware Roues *******************

    Route::middleware(['auth:sanctum','role:user'])
            ->get('/test-user',fn() => response()->json(['msg' => 'User Ok']));
    Route::middleware(['auth:sanctum','role:verifier'])
            ->get('/test-verifier',fn() => response()->json(['msg' => 'verifier Ok']));
    Route::middleware(['auth:sanctum','role:admin'])
            ->get('/test-admin',fn() => response()->json(['msg' => 'admin Ok']));

    // ***************************************************************
});


/*
   ************************   KYC Session APIS **************************
   ***********************       User Client Side Api              **************************

*/


Route::middleware(['auth:sanctum','role:user'])->prefix('v1')->group(function(){
    Route::post('/kyc/sessions',[KycSessionController::class,'store']);
    Route::get('/kyc/sessions/{uuid}',[KycSessionController::class,'show']);
});



// *******************************************************************************************




/*

 ************************* Verifier API ****************************************************


 *************************               ***************************************************

*/

Route::middleware(['auth:sanctum','role:verifier'])->prefix('v1')->group(function(){
    Route::get('/kyc-session-applications',[VerifierKycSessionController::class,'index']);
    Route::post('/kyc-sessions/accept',[VerifierKycSessionController::class,'accept']);
});
