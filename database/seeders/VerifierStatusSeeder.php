<?php

namespace Database\Seeders;

use App\Models\KycUser;
use App\Models\VerifierStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VerifierStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $verifiers = KycUser::where('role','verifier')->get();
        foreach ($verifiers as $verifier) {
            VerifierStatus::firstOrCreate(
                ['verifier_id' => $verifier->id],
                [
                    'status' => 'offline',
                    'active_session_id' => null,
                    'last_seen_at' => null,
                ]
            );
        }


    }
}
