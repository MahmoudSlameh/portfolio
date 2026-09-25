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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('active_template')->default('changelog');
            $table->string('site_name');
            $table->string('title_separator', 8)->default('—');
            $table->string('meta_description', 300)->nullable();
            $table->string('twitter_handle')->nullable();
            $table->string('google_site_verification')->nullable();
            $table->string('bing_site_verification')->nullable();
            $table->text('analytics_snippet')->nullable();
            $table->json('enabled_pages')->nullable();
            $table->string('contact_recipient')->nullable();
            $table->boolean('indexable')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
