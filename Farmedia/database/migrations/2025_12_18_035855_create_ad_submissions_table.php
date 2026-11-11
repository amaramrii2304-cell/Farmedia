<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ad_submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mitra_id')->constrained('mitras')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // home / category
            $table->string('placement', 20)->default('home');

            // karena kategori ada di products.kategori_produk (string)
            // kita simpan target kategori untuk placement=category
            $table->string('target_kategori', 255)->nullable();

            // snapshot kategori produk saat diajukan (biar konsisten)
            $table->string('kategori_snapshot', 255)->nullable();

            // status alur
            // draft -> pending -> approved -> active -> ended
            // reject juga
            $table->string('status', 20)->default('pending');

            // periode tayang (diisi admin saat approve/activate)
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();

            // pembayaran transfer
            $table->string('payment_method', 20)->default('transfer'); // fixed
            $table->string('payment_status', 20)->default('unpaid');   // unpaid/paid
            $table->string('payment_proof')->nullable();               // path
            $table->timestamp('paid_at')->nullable();

            // harga paket iklan (bisa fixed dulu)
            $table->unsignedInteger('price')->default(0);

            $table->text('admin_note')->nullable();

            $table->timestamps();

            $table->index(['status', 'placement']);
            $table->index(['start_at', 'end_at']);
            $table->unique(['product_id', 'status'], 'uniq_product_active_pending'); // optional, minimal anti spam
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_submissions');
    }
};
