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
}
