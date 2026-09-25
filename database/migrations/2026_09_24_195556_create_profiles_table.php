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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('initials', 4)->nullable();
            $table->string('role');
            $table->string('headline', 300)->nullable();
            $table->json('focus_areas')->nullable();
            $table->text('summary')->nullable();
            $table->json('story')->nullable();
            $table->string('location')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('timezone_label')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('current_version')->nullable();
            $table->string('availability_status')->default('open');
            $table->string('availability_label')->nullable();
            $table->string('availability_note')->nullable();
            $table->json('latest_release')->nullable();
            $table->json('stats')->nullable();
            $table->json('status')->nullable();
            $table->json('principles')->nullable();
            $table->string('portrait_alt')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
