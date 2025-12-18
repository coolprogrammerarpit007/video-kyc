<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\KycSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;
use App\Events\VerifierJoined;
use App\Events\VerifierJoinedSession;

class VerifierKycSessionController extends Controller
{
    public function index()
    {
        try
        {
            $sessions = KycSession::getAllPendingSessions();

            return response()->json([
                'status' => true,
                'msg' => 'All sessions fetched successfully',
                'sessions' => $sessions
            ])->setStatusCode(200);
        }

        catch(\Exception $e)
        {
            return response()->json([
                'status' => false,
                'msg' => 'Failed to fetch pending kyc sessions',
                'errors' => $e->getMessage()
            ],500);
        }
    }


    public function accept(Request $request)
    {
        try
        {

            $verifier = $request->user();

            DB::beginTransaction();

            $session = KycSession::where('id',$request->uuid)->lockForUpdate()->first();

            if(!$session)
            {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'msg' => 'KYC session not found!'
                ],404);
            }

            // check if session is not attended by any verifier yet and state is pending
            if(!$session->isAvailable())
            {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'msg' => 'Unavailable Kyc Session'
                ])->setStatusCode(409);
            }

            if($session->isExpired())
            {
                $session->update(['status' => 'expired']);
                DB::commit();
                return response()->json([
                    'status' => false,
                    'msg' => 'KYC session has been expired'
                ])->setStatusCode(410);
            }

            //  Assign Verifier
            $session->update([
                'verifier_id' => $verifier->id,
                'status' => 'in_progress',
                'assigned_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            DB::commit();

            event(new VerifierJoined(
                $session->uuid,
                $verifier->id
            ));

            return response()->json([
                'status' => true,
                'message' => 'Kyc Session accepted successfully!',
                'data' => $session
            ],200);

        }

        catch(\Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to accept Kyc session',
                'errors' => $e->getMessage()
            ],500);
        }
    }


    public function verifierJoin(Request $request)
    {
        try
        {
            $verifier = $request->user();
            $validator = Validator::make($request->all(),[
                'uuid' => 'required|string'
            ]);

            if($validator->fails())
            {
                return response()->json([
                    'status' => false,
                    'msg' => 'Validation fails',
                    'errors' => $validator->errors()
                ],422);
            }

            $validated_data = $validator->validate();
            $session = KycSession::where('uuid',$validated_data['uuid'])->first();

            // Must be assigned verifier

            if($session->verifier_id != $verifier->id)
            {
                return response()->json([
                    'status' => false,
                    'message' => 'Un-Authenticated Verifier Assigned'
                ],403);
            }

            if($session->status != 'in_progress')
            {
                return response()->json([
                    'status' => false,
                    'message' => 'Session Not Active'
                ],409);
            }


            // prevent verifier duplication

            if($session->verifier_joined_at)
            {
                return response()->json([
                    'status' => true,
                    'message' => 'Validator is already in the session'
                ],200);
            }

            DB::transaction(function () use ($session) {
                $session::update([
                    'verifier_joined_at' => Carbon::now()
                ]);
            });


            event(new VerifierJoinedSession(
                $session->uuid,
                $verifier->id
            ));

            return response()->json([
                'status' => true,
                'message' => 'verifier join the session successfully!'
            ],200);
        }

        catch(\Exception $e)
        {
            return response()->json([
                'status' => true,
                'message' => 'something happen at joining session. please try again later!',
                'errors' => $e->getMessage()
            ]);
        }
    }
}
