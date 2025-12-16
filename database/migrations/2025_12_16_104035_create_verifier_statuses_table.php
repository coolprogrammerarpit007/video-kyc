<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('verifier_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verifier_id')->unique()->constrained('kyc_users')->cascadeOnDelete();
            $table->string('status')->default('offline');
            // offline/available/busy
            $table->foreignId('active_session_id')->nullable()->constrained('kyc_sessions')->nullOnDelete();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifier_statuses');
    }
};
