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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // --- Auth Data ---
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // --- Profile Data (Sesuai Poin 10) ---
            $table->string('phone')->nullable();
            $table->string('country')->nullable(); // Penting untuk internasional
            $table->string('organization')->nullable();
            
            // --- Membership Core Logic ---
            $table->string('membership_type')->nullable(); // Gold, Silver, Bronze
            $table->string('member_id')->nullable()->unique(); // Generated saat approve
            
            $table->date('join_date')->nullable();
            $table->date('expiry_date')->nullable();
            
            // --- Status Management ---
            // registered, waiting_payment, waiting_verification, active, expired, dll
            $table->string('status')->default('registered')->index();
            
            // --- Role Access (Penting untuk Filament Panel) ---
            $table->string('role')->default('member'); // 'admin' atau 'member'
            
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
