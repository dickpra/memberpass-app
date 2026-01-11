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
        Schema::create('membership_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: Silver, Bronze, Gold
            $table->decimal('price', 15, 2)->default(0);
            $table->json('benefits')->nullable(); // Menyimpan list fasilitas (Array)
            $table->string('css_class')->nullable(); // Untuk mapping warna CSS (silver, bronze, gold)
            $table->boolean('is_active')->default(true);
            $table->boolean('is_invitation_only')->default(false); // Untuk Gold
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_tiers');
    }
};
