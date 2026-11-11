<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // opsional: kalau mau tau review ini dari order mana
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');

            $table->unsignedTinyInteger('rating'); // 1–5
            $table->text('comment')->nullable();

            $table->timestamps();

            // 1 user hanya boleh 1 review per produk
            $table->unique(['product_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
