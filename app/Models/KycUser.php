<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\Model;

class KycUser extends Authenticatable
{
    use HasApiTokens,HasFactory,Notifiable;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'last_login_at'
    ];

    /**
     * Hidden attributes for serialization
     */
    protected $hidden = [
        'password',
        'remember_token'
    ];

    // Attribute Casting

    protected $casts = [
        'last_login_at' => 'datetime'
    ];


    /**
     * Relationship
     */

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function kycSessions()
    {
        return $this->hasMany(KycSession::class,'user_id','id');
    }


    /* ============================
     |  Role Helper Methods
     |============================ */

    public function isUser():bool
    {
        return $this->role == 'user';
    }

    public function isVerifier():bool
    {
        return $this->role == 'verifier';
    }

    public function isAdmin():bool
    {
        return $this->role == 'admin';
    }

    /* ============================
     |  Status Helper Methods
     |============================ */

     public function isActive():bool
     {
        return $this->status == 'active';
     }

     public function isBlocked():bool
     {
        return $this->status == 'blocked';
     }

     public function isOffline():bool
     {
        return $this->status == 'offline';
     }

}
