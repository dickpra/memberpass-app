<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donation_programs', function (Blueprint $table) {
            $table->softDeletes(); // Ini nambah kolom 'deleted_at'
        });
    }

    public function down(): void
    {
        Schema::table('donation_programs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
