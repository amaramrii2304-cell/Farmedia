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
    Schema::create('orders', function (Blueprint $table) {
        $table->id(); // order_id
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->unsignedBigInteger('toko_id')->nullable(); // nanti bisa di-relasi ke tabel toko
        $table->decimal('total_harga', 15, 2);
        $table->decimal('ongkir', 15, 2)->default(0);
        $table->string('metode_pembayaran');
        $table->text('alamat_pengiriman');
        $table->string('status_order')->default('menunggu_konfirmasi');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
