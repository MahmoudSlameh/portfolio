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
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('services_kicker')->nullable()->after('indexable');
            $table->string('services_title')->nullable()->after('services_kicker');
            $table->string('services_highlight')->nullable()->after('services_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['services_kicker', 'services_title', 'services_highlight']);
        });
    }
};
