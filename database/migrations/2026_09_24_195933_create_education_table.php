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
        Schema::create('education', function (Blueprint $table) {
            $table->id();
            $table->string('degree');
            $table->string('institution');
            $table->string('institution_url')->nullable();
            $table->string('field_of_study')->nullable();
            $table->string('grade')->nullable();
            $table->char('country_code', 2)->nullable();
            $table->string('city')->nullable();
            $table->date('start_date')->index();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->json('achievements')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education');
    }
};
