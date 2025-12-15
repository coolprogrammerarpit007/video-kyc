<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('v1')->group(function(){
    Route::post('/auth/register',[AuthController::class,'register']);
    Route::post('/auth/login',[AuthController::class,'login']);
    Route::middleware(['auth:sanctum','role:user'])->post('/auth/logout',[AuthController::class,'logout']);


    // ***************** Testing Middleware Roues *******************

    Route::middleware(['auth:sanctum','role:user'])
            ->get('/test-user',fn() => response()->json(['msg' => 'User Ok']));
    Route::middleware(['auth:sanctum','role:verifier'])
            ->get('/test-verifier',fn() => response()->json(['msg' => 'verifier Ok']));
    Route::middleware(['auth:sanctum','role:admin'])
            ->get('/test-admin',fn() => response()->json(['msg' => 'admin Ok']));

    // ***************************************************************
});
