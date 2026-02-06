<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TABEL PROGRAM DONASI (Campaigns)
        Schema::create('donation_programs', function (Blueprint $table) {
            $table->id();
            $table->string('title'); 
            $table->string('slug')->unique(); 
            $table->text('description')->nullable();
            $table->string('banner_image')->nullable(); 
            
            // Target
            $table->decimal('target_amount', 15, 2)->nullable();
            $table->string('target_currency')->default('USD'); 
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. TABEL REKENING KHUSUS DONASI (Updated: Detail Lengkap International)
        Schema::create('donation_payment_methods', function (Blueprint $table) {
            $table->id();
            
            // Info Utama
            $table->string('method_type'); // 'paypal', 'bank_transfer', 'other'
            $table->string('provider_name'); // Nama Bank (e.g. "Bank Central Asia" atau "PayPal")
            $table->string('account_number'); // No Rekening / Email PayPal
            $table->string('account_owner'); // Atas Nama
            $table->string('currency_code', 3)->default('USD'); // Mata Uang Utama Akun ini
            
            // --- DETAIL TAMBAHAN (BANK TRANSFER INTERNATIONAL) ---
            // Kolom ini nullable karena PayPal tidak butuh ini
            $table->string('swift_code')->nullable(); // SWIFT / BIC Code (Wajib untuk International)
            $table->string('bank_city')->nullable(); // Kota Bank
            $table->string('bank_branch')->nullable(); // Cabang Bank
            $table->text('owner_address')->nullable(); // Alamat Terdaftar Pemilik Rekening (Wajib untuk Invoice/Receipt)
            $table->string('tax_number')->nullable(); // NPWP / VAT Number
            // ----------------------------------------------------

            $table->string('logo')->nullable(); 
            $table->text('instructions')->nullable(); // Instruksi tambahan custom
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. TABEL TRANSAKSI DONASI (History)
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('donation_program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('donation_payment_method_id')->nullable()->constrained()->nullOnDelete(); 
            
            // Data Nominal
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3); // USD, IDR (Apa yang dipilih member)
            
            // Data Bukti Bayar
            $table->string('sender_name')->nullable(); // Nama pengirim
            $table->string('proof_file')->nullable(); // Screenshot
            
            // Status & Notes
            $table->string('status')->default('pending_verification'); 
            $table->text('admin_note')->nullable(); 
            $table->text('donor_message')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
        Schema::dropIfExists('donation_payment_methods');
        Schema::dropIfExists('donation_programs');
    }
};