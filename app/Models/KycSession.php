<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KycSession extends Model
{
    //

    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'verifier_id',
        'status',
        'assignment_type',
        'requested_at',
        'assigned_at',
        'completed_at',
        'expired_at',
    ];


    protected $casts = [
        'requested_at' => 'datetime',
        'assigned_at'  => 'datetime',
        'completed_at' => 'datetime',
        'expired_at'   => 'datetime',
    ];

    // relationships

    public function kycUser()
    {
        return $this->belongsTo(KycUser::class,'user_id','id');
    }


    public static function getCurrentKycSession($user,$uuid)
    {
        $data = static::where('user_id',$user->id)->where('uuid',$uuid)->first();
        return $data;
    }

    public function isExpired(): bool
    {
        $session_expired =  $this->status != 'completed' && Carbon::now()->greaterThan($this->expired_at);
        return $session_expired;
    }

    public function isAvailable()
    {
        $is_available = $this->status == 'pending' && $this->verifier_id == null;
        return $is_available;
    }

    public function canStartVideo()
    {
        $permission = $this->status == 'in_progress' && $this->expired_at && Carbon::parse($this->expired_at)->isFuture() && $this->verifier_id != null && $this->verifier_joined_at != null and $this->user_joined_at != null;

        return $permission;
    }


    public static function getAllPendingSessions()
    {
        $data = static::with('kycUser:id,name,email,phone')->where('status','pending')->whereNull('verifier_id')->where('expired_at','>',Carbon::now())->orderBy('requested_at','asc')->get(['id','uuid','status','user_id','requested_at','expired_at']);

        return $data->map(function($session) {
            return [
                'id' => $session->id,
                'user_id' => $session->user_id,
                'status' => $session->status,
                'requested_date' => $session->requested_at->format('l,F j, Y'),
                'session_expiration_date' => $session->expired_at->format('l,F j, Y'),
                'user_name' => $session->kycUser->name,
                'user_email' => $session->kycUser?->email,
                'user_phone' => $session->kycUser?->phone,
                'session_join_link' => env('APP_URL')."/kyc/session/$session->uuid"
            ];
        });
    }
}
