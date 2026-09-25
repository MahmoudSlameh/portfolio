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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('author');
            $table->unsignedSmallInteger('published_year')->nullable();
            $table->string('category')->default('engineering');
            $table->string('status')->default('to-read');
            $table->date('finished_at')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->unsignedSmallInteger('pages')->nullable();
            $table->text('note')->nullable();
            $table->string('cover_background', 9)->default('#1d2b3a');
            $table->string('cover_ink', 9)->default('#f1ede4');
            $table->string('cover_accent', 9)->default('#e8a33d');
            $table->string('cover_style')->default('band');
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
