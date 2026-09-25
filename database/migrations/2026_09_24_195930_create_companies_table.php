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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('kind')->default('employer');
            $table->string('website_url')->nullable();
            $table->string('industry')->nullable();
            $table->string('city')->nullable();
            $table->char('country_code', 2)->nullable();
            $table->string('period_label')->nullable();
            $table->text('engagement')->nullable();
            $table->string('wordmark_style')->default('sans-bold');
            $table->boolean('is_featured')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
