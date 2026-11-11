<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ad_submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('ad_submissions', 'duration_days')) {
                $table->unsignedInteger('duration_days')->default(7)->after('placement');
            }
            // kalau kamu belum punya price / payment_status dll, skip ini
            if (!Schema::hasColumn('ad_submissions', 'price')) {
                $table->unsignedBigInteger('price')->default(0)->after('duration_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ad_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('ad_submissions', 'duration_days')) $table->dropColumn('duration_days');
            if (Schema::hasColumn('ad_submissions', 'price')) $table->dropColumn('price');
        });
    }
};
