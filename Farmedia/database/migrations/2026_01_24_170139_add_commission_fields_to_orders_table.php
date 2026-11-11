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
        Schema::table('orders', function (Blueprint $table) {

            // ✅ dibutuhkan oleh controller komisi (orders.mitra_id)
            if (!Schema::hasColumn('orders', 'mitra_id')) {
                $table->unsignedBigInteger('mitra_id')->nullable()->after('user_id');
                $table->index('mitra_id');
            }

            // ✅ dibutuhkan: komisi_produk + komisi_ongkir (fallback computeCommission masih boleh, tapi ini sebaiknya ada)
            if (!Schema::hasColumn('orders', 'komisi_produk')) {
                $table->decimal('komisi_produk', 15, 2)->default(0)->after('status_order');
            }

            if (!Schema::hasColumn('orders', 'komisi_ongkir')) {
                $table->decimal('komisi_ongkir', 15, 2)->default(0)->after('komisi_produk');
            }

            // ✅ dibutuhkan: patokan lunas (controller pakai komisi_lunas_at)
            if (!Schema::hasColumn('orders', 'komisi_lunas_at')) {
                $table->timestamp('komisi_lunas_at')->nullable()->after('komisi_ongkir');
            }

            // (opsional) order_no, kalau kamu mau simpan nomor order asli
            // controller sudah ada fallback FM-0001 kalau kolom ini tidak ada, jadi tidak wajib.
            // if (!Schema::hasColumn('orders', 'order_no')) {
            //     $table->string('order_no')->nullable()->after('id');
            //     $table->index('order_no');
            // }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            if (Schema::hasColumn('orders', 'komisi_lunas_at')) {
                $table->dropColumn('komisi_lunas_at');
            }

            if (Schema::hasColumn('orders', 'komisi_ongkir')) {
                $table->dropColumn('komisi_ongkir');
            }

            if (Schema::hasColumn('orders', 'komisi_produk')) {
                $table->dropColumn('komisi_produk');
            }

            if (Schema::hasColumn('orders', 'mitra_id')) {
                $table->dropIndex(['mitra_id']);
                $table->dropColumn('mitra_id');
            }

            // if (Schema::hasColumn('orders', 'order_no')) {
            //     $table->dropIndex(['order_no']);
            //     $table->dropColumn('order_no');
            // }
        });
    }
};
