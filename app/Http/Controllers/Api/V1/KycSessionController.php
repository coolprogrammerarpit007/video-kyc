<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\KycSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;



class KycSessionController extends Controller
{
    public function store()
    {
        try
        {
            $user = request()->user();
            DB::beginTransaction();
            $now = Carbon::now();
            $session = KycSession::create([
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'status' => 'pending',
                'requested_at' => now(),
                'expired_at' => $now->addHours(24),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' =>'Kyc session created successfully',
                'data' => [
                    'id' => $session->id,
                    'user_id' => $session->user_id,
                    'status' => $session->status,
                    'uuid' => $session->uuid,
                    'requested_at' => $session->requested_at,
                    'expired_at' => $session->expired_at,
                ],
                'session_join_link' => env('APP_URL') . "/kyc/session/$session->uuid"
            ],201);
        }

        catch(\Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Unsuccessful attempt at requesting for kyc session',
                'errors' => $e->getMessage()
            ],500);
        }
    }


    public function show(Request $request, string $uuid)
    {
        try
        {
            $user = $request->user();
            $session = KycSession::getCurrentKycSession($user,$uuid);

            if(!$session)
            {
                return response()->json([
                    'status' => false,
                    'msg' => 'session not found!'
                ],404);
            }

            // Expiry Condition
            if($session->isExpired())
            {
                $session->update(['status' => 'expired']);
            }

            // Permission for user
            $canStartVideo = $session->status == 'in_progress';
            $canUploadDocs = $session->status == 'in_progress';

            // UI STATE HINT
            $uiState = match($session->status)
            {
                'pending'     => 'waiting_for_verifier',
            'in_progress' => 'ready_to_start',
            'completed'   => 'session_completed',
            'expired'     => 'session_expired',
            default       => 'unknown',
            };

            return response()->json([
                'status' => true,
                'data' => [
                    'session' => [
                        'uuid' => $session->uuid,
                        'status' => $session->status,
                        'verifier_id' => $session->verifier_id,
                        'expired_at' => $session->expired_at,
                    ],
                    'permissions' => [
                        'start_video' => $canStartVideo,
                        'upload_video' => $canUploadDocs,
                    ],
                    'ui_hint' => [
                        'state' => $uiState,
                        'message' => match ($uiState) {
                        'waiting_for_verifier' => 'Please wait for verifier to join.',
                        'ready_to_start'       => 'Verifier has joined. You may start your KYC.',
                        'session_completed'    => 'KYC session completed.',
                        'session_expired'      => 'KYC session expired.',
                        default                => '',
                    },
                ]

                ]
                ],200);
        }

        catch(\Exception $e)
        {
            return response()->json([
                'status' => false,
                'msg' => 'Error occurs on KYC Session',
                'errors' => $e->getMessage()
            ],500);
        }
    }
}
