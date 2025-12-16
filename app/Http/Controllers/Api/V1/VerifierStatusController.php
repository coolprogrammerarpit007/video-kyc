<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\VerifierStatus;
use App\Services\KycAutoAssignmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VerifierStatusController extends Controller
{
    public function checkAvailablity(Request $request,KycAutoAssignmentService $autoAssignmentService)
    {
        try
        {
            $verifier = $request->user();
            $status = $request->status;
            $verifierStatus = VerifierStatus::where('verifier_id',$verifier->id)->first();
            if($verifierStatus)
            {
                response()->json([
                    'status' => false,
                    'message' => 'Verifier not found!',
                ],404);
            }

            if ($verifierStatus->status === 'busy') {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot go $status while busy with a session",
                ], 409);
            }

            $updated_status = $status == 'online' ? 'available' : 'offline';

            $verifierStatus->update([
                'status' => $updated_status,
                'last_seen_at' => Carbon::now()
            ]);

            //  Try Auto Assignment
            $autoAssignmentService->assign();

            return response()->json([
                'status' => true,
                'message' => 'Verifier status updated successfully',
                'verifier_status' => $verifierStatus->status
            ],200);
        }

        catch(\Exception $e)
        {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update verifier status',
                'error'   => config('app.debug') ? $e->getMessage() : 'Something went wrong',
            ], 500);
        }
    }
}
