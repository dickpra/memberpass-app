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
        Schema::create('payment_files', function (Blueprint $table) {
            $table->id();
            
            // Hapus file jika payment dihapus
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            
            $table->string('file_path'); // Lokasi file di storage
            $table->string('file_type')->nullable(); // jpg, pdf
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_files');
    }
};
