<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\KycUser;
use Illuminate\Support\Facades\Hash;

class VerifierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // prevent duplicate verifier creation

        $existing = KycUser::where('role','verifier')->first();
        if($existing)
        {
            $this->command->info('Verifier already existed');
            return;
        }

        $user = User::create([
            'name' => 'Verifier One',
            'email' => 'verifier@test.com',
            'password' => Hash::make('12345678')
        ]);

        $kycuser = KycUser::create([
            'name' => $user->name,
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => 'verifier',
            'status' => 'active',
            'password' => $user->password
        ]);

        $this->command->info('Test Verifier created successfully!');
    }
}
