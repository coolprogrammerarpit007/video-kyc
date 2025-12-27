<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\KycSession;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function getSessionData(Request $request)
    {
        try
        {
            $user = $request->user();
            $session = KycSession::where('user_id',$user->id)->where('status','pending')->first();
            if(!$session)
            {
                return response()->json([
                    'status' => true,
                    'message' => 'No current pending session available',
                    'data' => [
                        'user_name' => $user->name,
                        'session_status' => null
                    ]
                ],200);
            }

            return response()->json([
                'status' => true,
                'message' => 'dashboard data fetched successfully!',
                'data' => [
                    'user_name' => $user->name,
                    'uuid' => $session->uuid,
                    'session_status' => $session->status,
                    'verifier_id' => $session->verifier_id,
                    'expired_at' => $session->expired_at,
                    'user_joined_at' => $session->user_joined_at,
                    'verifier_joined_at' => $session->verifier_joined_at
                ]
                ],200);
        }

        catch(\Exception $e)
        {
            return response()->json([
                'status'=>false,
                'message' => 'something happen! try again after sometime!',
                'errors' => $e->getMessage()
            ],500);
        }
    }
}
