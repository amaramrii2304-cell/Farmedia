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
    Schema::create('products', function (Blueprint $table) {
        $table->id(); // produk_id

        // Menggunakan mitra_id sebagai foreign key
        $table->foreignId('mitra_id')->constrained('mitras')->onDelete('cascade');

        $table->string('nama_produk');
        $table->string('kategori_produk')->nullable();
        $table->text('deskripsi_produk')->nullable();
        $table->decimal('harga', 15, 2);
        $table->integer('stok')->default(0);
        $table->integer('berat')->nullable(); // dalam gram
        $table->string('foto_produk')->nullable(); // simpan path gambar

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
