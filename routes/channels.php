<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\KycUser;
use App\Models\KycSession;

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });


Broadcast::channel('kyc-session.{uuid}',function($user,$uuid){
    $session = KycSession::where('uuid',$uuid)->first();

    if(!$session)
    {
        return false;
    }

    // check if the user is the session owner

    if($user->role == 'user' && $session->user_id == $user->id)
    {
        return true;
    }

    //  check if user is the assigned verifier

    if($user->role == 'verifier' && $session->verifier_id == $user->id)
    {
        return true;
    }

    return false;
});
