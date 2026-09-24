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
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('organization_name')->nullable();
            $table->string('role');
            $table->string('employment_type')->default('full-time');
            $table->string('work_mode')->default('on-site');
            $table->char('country_code', 2)->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->date('start_date')->index();
            $table->date('end_date')->nullable();
            $table->text('summary')->nullable();
            $table->json('highlights')->nullable();
            $table->string('branch')->nullable();
            $table->string('version')->nullable();
            $table->string('commit_hash', 7)->nullable();
            $table->string('commit_message')->nullable();
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
        Schema::dropIfExists('experiences');
    }
};
