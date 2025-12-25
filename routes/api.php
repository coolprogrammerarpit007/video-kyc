<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\KycSessionController;
use App\Http\Controllers\Api\V1\VerifierKycSessionController;
use App\Http\Controllers\Api\V1\VerifierStatusController;
use App\Http\Controllers\Api\V1\WebRtcController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



//  *************** Logout API ***********************


// ***************************************************
Route::prefix('v1')->group(function(){
    Route::get('/check-health',[AuthController::class,'check_health']);
    Route::post('/auth/register',[AuthController::class,'register']);
    Route::post('/auth/login',[AuthController::class,'login']);
    Route::post('/auth/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');


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
    Route::post('/kyc-sessions/user/join',[KycSessionController::class,'join']);
});



// *******************************************************************************************




/*

 ************************* Verifier API ****************************************************


 *************************               ***************************************************

*/

Route::middleware(['auth:sanctum','role:verifier'])->prefix('v1')->group(function(){
    Route::get('/kyc-session-applications',[VerifierKycSessionController::class,'index']);
    Route::post('/kyc-sessions/accept',[VerifierKycSessionController::class,'accept']);
    Route::post('/kyc-sessions/verifier/join',[VerifierKycSessionController::class,'verifierJoin']);


    //  ************ Verifier Inter APIs **************

    Route::post('/verifier/status',[VerifierStatusController::class,'checkAvailablity']);
});



// ********************** Webhook and WebRTC API Routes ****************

Route::middleware('auth:sanctum')->prefix('v1/kyc/webrtc')->group(function(){

    Route::post('/offer',[WebRtcController::class,'sendOffer']);
    Route::post('/answer',[WebRtcController::class,'sendAnswer']);
    Route::post('/ice',[WebRtcController::class,'sendIce']);

});


// *********************************************************************
