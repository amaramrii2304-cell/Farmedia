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
    Schema::table('mitras', function (Blueprint $table) {
        if (!Schema::hasColumn('mitras', 'logo_path')) {
            $table->string('logo_path')->nullable()->after('alamat');
        }
    });
}

public function down(): void
{
    Schema::table('mitras', function (Blueprint $table) {
        $table->dropColumn('logo_path');
    });
}

};
