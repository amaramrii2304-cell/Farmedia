<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('commission_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mitra_id')->constrained('mitras')->cascadeOnDelete();

            // snapshot nominal saat invoice dibuat
            $table->decimal('amount', 15, 2)->default(0);   // pokok hutang komisi
            $table->decimal('penalty', 15, 2)->default(0);  // denda (diupdate saat bayar/approve)
            $table->decimal('total_due', 15, 2)->default(0);// amount + penalty

            $table->dateTime('due_date')->nullable();

            // unpaid | waiting_verification | paid | rejected
            $table->string('status')->default('unpaid');

            $table->string('payment_method')->nullable(); // transfer/manual
            $table->string('payment_proof_path')->nullable();
            $table->text('notes')->nullable();

            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            $table->index(['mitra_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_invoices');
    }
};
