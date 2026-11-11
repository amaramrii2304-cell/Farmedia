<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_cod_settlements_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cod_settlements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();

            // angka dasar
            $table->decimal('expected_amount', 12, 2)->default(0); // harusnya diterima dari kurir (umumnya = total_harga)
            $table->decimal('received_amount', 12, 2)->nullable(); // yang benar-benar diterima admin
            $table->timestamp('received_at')->nullable();
            $table->string('received_note', 255)->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();

            // potongan
            $table->decimal('platform_fee', 12, 2)->default(0); // komisi platform (misal komisi_produk + komisi_ongkir)
            $table->decimal('service_fee', 12, 2)->default(0);  // biaya layanan admin / handling
            $table->decimal('net_to_seller', 12, 2)->default(0); // sisa bayar ke penjual

            // status
            $table->enum('status', ['unverified','verified','selisih','paid'])->default('unverified');

            // payout ke mitra
            $table->enum('payout_status', ['unpaid','paid'])->default('unpaid');
            $table->timestamp('payout_at')->nullable();
            $table->string('payout_ref', 100)->nullable(); // no referensi transfer / catatan
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cod_settlements');
    }
};
