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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            
            // Info Wajib
            $table->string('bank_name');       // BCA, Mandiri, Chase
            $table->string('account_number');  // 123456789
            $table->string('account_owner');   // PT WFIED Indonesia
            
            // Info International / Detail
            $table->string('bank_city')->nullable();  // KCP Surabaya / New York Branch
            $table->string('swift_code')->nullable(); // Untuk transfer Luar Negeri
            
            // Tampilan
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
