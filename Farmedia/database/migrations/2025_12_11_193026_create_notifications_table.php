<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            // judul pendek notif
            $table->string('title');
            // isi / deskripsi notif
            $table->text('message')->nullable();
            // jenis notif (order, user, dll) — opsional
            $table->string('type')->nullable();
            // apakah sudah dibaca admin?
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
