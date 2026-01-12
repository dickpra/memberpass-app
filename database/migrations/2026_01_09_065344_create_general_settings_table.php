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
            
            // 1. CMS / Public Info
            $table->string('site_title')->default('WFIED Membership');
            $table->text('site_description')->nullable();
            $table->string('site_logo')->nullable();
            $table->string('footer_text')->nullable();
            
            // 2. Organization / Legal Info (Untuk Invoice)
            $table->string('organization_name')->nullable();    // Nama PT
            $table->text('organization_address')->nullable();   // Alamat Fisik
            $table->string('tax_number')->nullable();           // NPWP / VAT
            
            // 3. Dashboard Announcement
            $table->boolean('announcement_active')->default(true);
            $table->text('announcement_text')->nullable();
            
            // 4. Support Contact
            $table->string('support_phone')->nullable(); // WhatsApp
            $table->string('support_email')->nullable(); // Email
            
            // 5. System
            $table->string('currency')->default('IDR');

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
