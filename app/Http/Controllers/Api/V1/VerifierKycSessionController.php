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
    public function index(Request $request)
    {
        try {
            $verifier = $request->user();

            /**
             * Sessions that are still unassigned
             */
            $pendingSessions = KycSession::where('status', 'pending')
                ->whereNull('verifier_id')
                ->where('expired_at', '>', now())
                ->orderBy('created_at')
                ->get([
                    'uuid',
                    'user_id',
                    'created_at',
                    'expired_at'
                ]);

            /**
             * Verifier's currently active session (only one allowed)
             */
            $activeSession = KycSession::where('verifier_id', $verifier->id)
                ->whereIn('status', ['in_progress'])
                ->orderByDesc('assigned_at')
                ->first();

            /**
             * Past sessions handled by verifier
             */
            $pastSessions = KycSession::where('verifier_id', $verifier->id)
                ->whereIn('status', ['completed', 'expired'])
                ->orderByDesc('updated_at')
                ->get([
                    'uuid',
                    'status',
                    'completed_at',
                    'expired_at'
                ]);

            return response()->json([
                'status' => true,
                'message' => 'Verifier dashboard data fetched successfully',
                'data' => [
                    'pending_sessions' => $pendingSessions,
                    'active_session' => $activeSession,
                    'past_sessions' => $pastSessions
                ]
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch verifier dashboard',
                'errors' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }


        public function accept(Request $request)
    {
        try {
            $verifier = $request->user();

            DB::beginTransaction();

            $session = KycSession::where('uuid', $request->uuid)
                ->lockForUpdate()
                ->first();

            if (!$session) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'KYC session not found'
                ], 404);
            }

            if ($session->isExpired()) {
                $session->update(['status' => 'expired']);
                DB::commit();

                return response()->json([
                    'status' => false,
                    'message' => 'KYC session has expired'
                ], 410);
            }

            if (!$session->isAvailable()) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Session already taken'
                ], 409);
            }

            $session->update([
                'verifier_id' => $verifier->id,
                'status' => 'in_progress',
                'assigned_at' => now()
            ]);

            DB::commit();

            event(new VerifierJoined(
                $session->uuid,
                $verifier->id
            ));

            return response()->json([
                'status' => true,
                'message' => 'Session accepted successfully',
                'data' => [
                    'uuid' => $session->uuid,
                    'status' => $session->status
                ]
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to accept session',
                'errors' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }


        public function verifierJoin(Request $request)
    {
        try {
            $verifier = $request->user();

            $request->validate([
                'uuid' => 'required|string'
            ]);

            $session = KycSession::where('uuid', $request->uuid)->firstOrFail();

            if ($session->verifier_id !== $verifier->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized verifier'
                ], 403);
            }

            if ($session->status !== 'in_progress') {
                return response()->json([
                    'status' => false,
                    'message' => 'Session is not active'
                ], 409);
            }

            if ($session->verifier_joined_at) {
                return response()->json([
                    'status' => true,
                    'message' => 'Verifier already joined'
                ], 200);
            }

            $session->update([
                'verifier_joined_at' => now()
            ]);

            event(new VerifierJoinedSession(
                $session->uuid,
                $verifier->id
            ));

            return response()->json([
                'status' => true,
                'message' => 'Verifier joined the session'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to join session',
                'errors' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }



}
