<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Articles get a language (and with it a reading direction), a series, a list of sources and a video.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('language', 5)->default('en')->after('excerpt');
            $table->string('series')->nullable()->after('tags');
            $table->unsignedSmallInteger('series_order')->nullable()->after('series');
            $table->json('sources')->nullable()->after('series_order');
            $table->string('video_url', 2048)->nullable()->after('sources');

            $table->index(['series', 'series_order']);
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['series', 'series_order']);
            $table->dropColumn(['language', 'series', 'series_order', 'sources', 'video_url']);
        });
    }
};
