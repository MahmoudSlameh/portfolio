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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('tagline')->nullable();
            $table->text('summary')->nullable();
            $table->unsignedSmallInteger('year');
            $table->string('status')->default('live');
            $table->string('category')->default('product');
            $table->boolean('is_featured')->default(false)->index();
            $table->string('version')->nullable();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('experience_id')->nullable()->constrained()->nullOnDelete();
            $table->string('role')->nullable();
            $table->string('team')->nullable();
            $table->string('timeline')->nullable();
            $table->json('overview')->nullable();
            $table->json('problem')->nullable();
            $table->json('approach')->nullable();
            $table->json('architecture')->nullable();
            $table->json('features')->nullable();
            $table->json('challenges')->nullable();
            $table->json('metrics')->nullable();
            $table->json('links')->nullable();
            $table->string('cover_alt')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_published', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
