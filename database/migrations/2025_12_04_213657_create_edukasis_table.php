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
        Schema::create('edukasis', function (Blueprint $table) {
            $table->id();

            // Sesuai model dan index.blade.php
            $table->string('judul');                 // Judul konten edukasi
            $table->string('kategori')->nullable();  // Kategori (opsional)
            $table->string('link_video')->nullable(); // Link video (YouTube / lainnya)
            $table->text('isi')->nullable();         // Isi artikel edukasi

            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edukasis');
    }
};
