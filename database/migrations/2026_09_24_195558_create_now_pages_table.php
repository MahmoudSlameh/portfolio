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
        Schema::create('now_pages', function (Blueprint $table) {
            $table->id();
            $table->string('location')->nullable();
            $table->string('availability')->nullable();
            $table->json('focus')->nullable();
            $table->json('learning')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('now_pages');
    }
};
