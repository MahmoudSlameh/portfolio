<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Studio templates (docs/12-ai-templates.md §4): a template is a row here, its designs are versions.
 * The active version is linked after both tables exist (circular reference).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studio_templates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('source')->default('manual');
            $table->string('status')->default('draft')->index();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('current_step')->nullable();
            $table->text('error')->nullable();
            $table->unsignedBigInteger('active_version_id')->nullable();
            $table->timestamps();
        });

        Schema::create('studio_template_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('studio_template_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->json('spec');
            $table->json('notes')->nullable();
            $table->text('prompt')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('studio_template_versions')->nullOnDelete();
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->timestamps();

            $table->unique(['studio_template_id', 'number']);
        });

        Schema::table('studio_templates', function (Blueprint $table) {
            $table->foreign('active_version_id')->references('id')->on('studio_template_versions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('studio_templates', function (Blueprint $table) {
            $table->dropForeign(['active_version_id']);
        });
        Schema::dropIfExists('studio_template_versions');
        Schema::dropIfExists('studio_templates');
    }
};
