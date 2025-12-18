<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\WebRtcIceCandidateSent;
use App\Events\WebRtcOfferSent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\KycSession;
use Illuminate\Support\Facades\Validator;

class WebRtcController extends Controller
{


    private function getReadySession(string $uuid)
    {
        $session = KycSession::where('uuid',$uuid)->firstOrFail();

        if(!$session->canStartVideo())
        {
            abort(409,"Video can not be started yet!");
        }

        return $session;
    }

    // Send Offer

    public function sendOffer(Request $request)
    {
        try
        {
            $validated = Validator::make($request->all(),[
                'uuid' => 'required|string',
                'sdp' => 'required|string',
                'type' => 'required|in:offer'
            ]);

            if($validated->fails())
            {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error!'
                ],422);
            }

            $session = $this->getReadySession($request->uuid);

            event(new WebRtcOfferSent(
                $session->uuid,
                $request->only('type','sdp')
            ));

            return response()->json([
                'status' => true,
                'message' => 'offer sent successfully!'
            ],200);

        }

        catch(\Exception $e)
        {
            return response()->json([
                'status' => false,
                'message' => 'something occurs! while opening video.try again later',
                'errors' => $e->getMessage()
            ],500);
        }
    }


    public function sendAnswer(Request $request)
    {
        try
        {
            $validated = Validator::make($request->all(),[
                'uuid' => 'required|string',
                'sdp' => 'required|string',
                'type' => 'required|in:answer'
            ]);

            if($validated->errors())
            {
                return response()->json([
                    'status' => false,
                    'message' => 'failed to sent sdp answer'
                ],422);
            }

            $session = $this->getReadySession($request->uuid);

            event(new WebRtcOfferSent(
                $session->uuid,$request->only('type','sdp'
            )));

            return response()->json([
                'status' => true,
                'message' => 'sent answer successfully!'
            ],200);
        }

        catch(\Exception $e)
        {
            return response()->json([
                'status' => false,
                'message' => 'something occurs! while opening video.try again later',
                'errors' => $e->getMessage()
            ],500);
        }
    }


    public function sendIce(Request $request)
    {
        try
        {
            $validated = Validator::make($request->all(),[
                'uuid'             => 'required|string',
                'candidate'        => 'required|string',
                'sdpMid'           => 'nullable|string',
                'sdpMLineIndex'    => 'nullable|integer',
            ],);

            if($validated->fails())
            {
                return response()->json([
                    'status' => false,
                    'message' => 'failed to make communication'
                ],422);
            }

            $session = $this->getReadySession($request->uuid);

            event(new WebRtcIceCandidateSent(
                $session->uuid,
                $request->only('candidate','sdpMid','sdpMLineIndex')
            ));

            return response()->json([
                'status' => true,
                'message' => 'successfully message transfer!'
            ],200);



        }

        catch(\Exception $e)
        {
            return response()->json([
                'status' => false,
                'message' => 'something occurs! while opening video.try again later',
                'errors' => $e->getMessage()
            ],500);
        }
    }


}
