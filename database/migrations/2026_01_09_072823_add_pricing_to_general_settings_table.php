<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            // Kita simpan harga default untuk Silver & Bronze
            $table->decimal('silver_price', 15, 2)->default(0);
            $table->decimal('bronze_price', 15, 2)->default(0);
            
            // Gold biasanya custom, tapi kita kasih default base price jika perlu
            $table->decimal('gold_price', 15, 2)->default(0); 
        });
    }

    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn(['silver_price', 'bronze_price', 'gold_price']);
        });
    }
};
