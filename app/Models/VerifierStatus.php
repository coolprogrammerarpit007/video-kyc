<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifierStatus extends Model
{
    use HasFactory;

    protected $table = 'verifier_statuses';

    protected $fillable = [
        'verifier_id',
        'status',
        'active_session_id',
        'last_seen_at',
    ];


    protected $casts = [
        'last_seen_at' => 'datetime',
    ];



}
