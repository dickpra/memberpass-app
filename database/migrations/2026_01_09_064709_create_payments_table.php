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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke User
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Detail Transfer
            $table->decimal('amount', 15, 2)->default(0); 
            $table->string('currency', 3)->default('IDR'); 
            $table->string('sender_name')->nullable();
            $table->string('sender_bank')->nullable();
            
            // Jenis: 'registration' (baru) atau 'renewal' (perpanjang)
            $table->string('type')->default('registration');
            
            // Status Pembayaran
            // waiting_verification, approved, rejected
            $table->string('status')->default('waiting_verification');
            
            // Admin Audit
            $table->text('admin_note')->nullable(); // Alasan reject
            $table->unsignedBigInteger('verified_by')->nullable(); // ID Admin
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
