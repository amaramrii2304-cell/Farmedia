<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('commission_invoice_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_invoice_id')
                ->constrained('commission_invoices')
                ->cascadeOnDelete();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['commission_invoice_id', 'order_id']);
            $table->index(['order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_invoice_orders');
    }
};
