<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\KycSession;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function getSessionData(Request $request)
{
    try {
        $user = $request->user();

        /**
         * Active session
         * Only ONE can exist at a time
         */
        $activeSession = KycSession::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderByDesc('created_at')
            ->first();

        /**
         * Past sessions (history)
         */
        $pastSessions = KycSession::where('user_id', $user->id)
            ->whereIn('status', ['completed', 'expired'])
            ->orderByDesc('created_at')
            ->get([
                'uuid',
                'status',
                'expired_at',
                'completed_at',
                'created_at'
            ]);

        return response()->json([
            'status' => true,
            'message' => 'Dashboard data fetched successfully',
            'data' => [
                'user_name' => $user->name,

                'active_session' => $activeSession ? [
                    'uuid' => $activeSession->uuid,
                    'status' => $activeSession->status,
                    'verifier_id' => $activeSession->verifier_id,
                    'expired_at' => $activeSession->expired_at,
                    'user_joined_at' => $activeSession->user_joined_at,
                    'verifier_joined_at' => $activeSession->verifier_joined_at,
                    'created_at' => $activeSession->created_at,
                ] : null,

                'past_sessions' => $pastSessions
            ]
        ], 200);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong while fetching dashboard data',
            'errors' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

}
