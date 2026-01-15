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
        Schema::table('membership_tiers', function (Blueprint $table) {
            // Harga IDR (Format besar, tanpa desimal)
            $table->decimal('price_idr', 15, 0)->default(0)->after('price');
            $table->decimal('original_price_idr', 15, 0)->nullable()->after('price_idr');

            // Harga USD (Format kecil, pakai desimal)
            $table->decimal('price_usd', 10, 2)->default(0)->after('original_price_idr');
            $table->decimal('original_price_usd', 10, 2)->nullable()->after('price_usd');
        });

        // Tambah kolom 'site_currency' di general_settings jika belum ada
        Schema::table('general_settings', function (Blueprint $table) {
            // Default IDR
            $table->string('site_currency')->default('IDR')->after('site_title'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_tiers', function (Blueprint $table) {
            //
        });
    }
};
