<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P9-04: one row per AI generation attempt (usage and the daily limit).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studio_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('studio_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('queued');
            $table->text('prompt')->nullable();
            $table->string('start_from')->nullable();
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->unsignedTinyInteger('turns')->default(0);
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_generations');
    }
};
