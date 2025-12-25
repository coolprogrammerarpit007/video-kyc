<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Models\KycUser;
use App\Models\User;

use function Symfony\Component\Clock\now;

class AuthController extends Controller
{


    public function check_health()
    {
        try
        {
            return response()->json([
                'status' => true,
                'msg' => 'api working correctly!'
            ],200);
        }

        catch(\Exception $e)
        {
            return response()->json([
                'status' => false,
                'errors' => $e->getMessage()
            ],500);
        }
    }

    public function register(Request $request)
    {
        try
        {
            $validated = Validator::make($request->all(),[
                'name' => 'required|string|max:20',
                'email' => 'required|email|max:45|unique:kyc_users,email',
                'phone' => 'nullable|string|max:10',
                'password' => 'required|string|min:8'
            ]);

            if($validated->fails())
            {
                return response()->json([
                    'status' => false,
                    'msg' => 'Validation fails',
                    'errors' => $validated->errors()
                ])->setStatusCode(422,"Validation Error!");
            }

            $validated = $validated->validate();
            DB::beginTransaction();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password'])
            ]);

            $kyc_user = KycUser::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => $user->password,
                'role' => 'user',
                'status' => 'active'
            ]);

            $token = $kyc_user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
                'data' => $kyc_user,
                'token' => $token
            ]);
        }

        catch(\Exception $e)
        {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'msg' => 'User Registration Fails!',
                'errors' => $e->getMessage()
            ]);
        }
    }


    public function login(Request $request)
    {
        try
        {
            $validated = Validator::make($request->all(),[
                'email' => 'required|email',
                'password' => 'required|string'
            ]);

            if($validated->fails())
            {
                return response()->json([
                    'status' => false,
                    'msg'=> 'Validation Fails',
                    'errors' => $validated->errors()
                ])->setStatusCode(422);
            }

            $validated = $validated->validate();

            $kyc_user = KycUser::where('email',$validated['email'])->first();

            if(!$kyc_user)
            {
                return response()->json([
                    'status' => false,
                    'msg' => 'Invalid Login credentials'
                ],401);
            }

            if(!Hash::check($validated['password'],$kyc_user->password))
            {
                return response()->json([
                    'status' => false,
                    'msg' => 'Invalid Login credentials'
                ])->setStatusCode(401);
            }

            if($kyc_user->status != 'active')
            {
                return response()->json([
                    'status' => false,
                    'msg' => 'Account is not Active'
                ])->setStatusCode(403);
            }

            $token = $kyc_user->createToken('auth_token')->plainTextToken;

            $kyc_user->update([
                'last_login_at' => now()
            ]);


            return response()->json([
                'status' => true,
                'msg' => 'User Login successfully!',
                'data' => $kyc_user,
                'token' => $token
            ]);
        }

        catch(\Exception $e)
        {
            response()->json([
                'status' => false,
                'msg' => 'User Login fails',
                'errors' => $e->getMessage()
            ]);
        }
    }


    public function logout(Request $request)
    {
        try
        {
            // delete current Access Tokens
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'status' => true,
                'msg' => 'User Logout successfully!'
            ]);
        }

        catch(\Exception $e)
        {
            response()->json([
                'status' => false,
                'msg' => 'User Logout fails',
                'errors' => $e->getMessage()
            ]);
        }
    }
}
