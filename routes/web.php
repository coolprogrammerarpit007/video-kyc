<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\KycUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('launching-soon');
});


Route::get('/verifier',function(){
    try
    {
        DB::beginTransaction();
        $user = User::create([
            'name' => 'Verifier One',
            'email' => 'verifier@test.com',
            'password' => Hash::make('12345678')
        ]);

        $kycuser = KycUser::create([
            'name' => $user->name,
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => 'verifier',
            'status' => 'active',
            'password' => $user->password
        ]);
        DB::commit();
        return redirect('/')->with('success',"Verifier created successfully!");
    }

    catch(\Exception $e)
    {
        DB::rollBack();
        return redirect('/')->with('error',$e->getMessage());
    }
});
