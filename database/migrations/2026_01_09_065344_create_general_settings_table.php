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
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            
            // --- A. Organization Info ---
            $table->string('organization_name')->default('My Organization');
            $table->text('organization_address')->nullable();
            $table->string('vat_number')->nullable(); // NPWP / VAT
            
            // --- B. Bank Detail ---
            $table->string('bank_name')->nullable(); // BCA, Mandiri, JP Morgan
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_owner')->nullable(); // Atas Nama
            $table->string('bank_city')->nullable(); // Cabang/Kota
            
            // --- C. International Info ---
            $table->string('bank_swift_code')->nullable(); // Wajib utk internasional
            $table->string('currency')->default('IDR'); // IDR / USD
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_settings');
    }
};
