<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Hapus kolom user_id dan toko_id kalau masih ada
            if (Schema::hasColumn('products', 'user_id')) {
                $table->dropColumn('user_id');
            }

            if (Schema::hasColumn('products', 'toko_id')) {
                $table->dropColumn('toko_id');
            }

            // Tambah kolom mitra_id
            if (!Schema::hasColumn('products', 'mitra_id')) {
                $table->foreignId('mitra_id')
                      ->after('id')
                      ->constrained('mitras')
                      ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // rollback: hapus mitra_id, balikin user_id & toko_id
            if (Schema::hasColumn('products', 'mitra_id')) {
                $table->dropForeign(['mitra_id']);
                $table->dropColumn('mitra_id');
            }

            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('toko_id')->nullable();
        });
    }
};
