<?php

namespace App\Services;

use App\Models\KycUser;
use App\Models\KycSession;
use App\Models\VerifierStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class KycAutoAssignmentService
{
    /**
     * Try to auto assign one pending kyc session.
     * Safe to call multiple times
     */

    public function assign():bool
    {
        return DB::transaction(function(){

            // Lock Oldest Pending,unassigned Expiry Session
            $session = KycSession::where('status','pending')
                                    ->whereNull('verifier_id')
                                    ->where('expired_at','>',Carbon::now())
                                    ->orderBy('requested_at','asc')
                                    ->lockForUpdate()
                                    ->first();

            if(!$session)
            {
                return false; // nothing to assign
            }

            // Find first available verifiers

            $verifierStatus = VerifierStatus::where('status','available')->whereNull('active_session_id')
                                        ->lockForUpdate()
                                        ->first();

            if(!$verifierStatus)
            {
                return false; // Assign to no one
            }

            // If all their is pending session and available verifier then assign verifier to the session

            $session->update([
                'verifier_id' => $verifierStatus->verifier_id,
                'status' => 'in_progress',
                'assigned_at' => Carbon::now(),
            ]);

            // update verifier status to busy

            $verifierStatus->update([
                'status' => 'busy',
                'active_session_id' => $session->id
            ]);

            return true;
        });
    }



}
