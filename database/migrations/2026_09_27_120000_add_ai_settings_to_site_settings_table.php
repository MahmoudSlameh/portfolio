<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P9-02: AI settings (Site → AI). Empty values fall back to `.env` (config/studio.php → ai).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->boolean('ai_enabled')->default(true);
            $table->string('ai_provider')->nullable();
            $table->string('ai_model')->nullable();
            $table->text('ai_api_key')->nullable(); // encrypted cast
            $table->string('ai_base_url')->nullable();
            $table->unsignedSmallInteger('ai_daily_limit')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn(['ai_enabled', 'ai_provider', 'ai_model', 'ai_api_key', 'ai_base_url', 'ai_daily_limit']);
        });
    }
};
