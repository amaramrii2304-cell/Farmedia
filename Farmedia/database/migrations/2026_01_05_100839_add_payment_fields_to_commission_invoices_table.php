<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('commission_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('commission_invoices', 'payment_method')) {
                $table->string('payment_method', 50)->nullable()->after('status');
            }
            if (!Schema::hasColumn('commission_invoices', 'payment_note')) {
                $table->text('payment_note')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('commission_invoices', 'payment_proof_path')) {
                $table->string('payment_proof_path')->nullable()->after('payment_note');
            }
            if (!Schema::hasColumn('commission_invoices', 'payment_submitted_at')) {
                $table->timestamp('payment_submitted_at')->nullable()->after('payment_proof_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('commission_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('commission_invoices', 'payment_submitted_at')) {
                $table->dropColumn('payment_submitted_at');
            }
            if (Schema::hasColumn('commission_invoices', 'payment_proof_path')) {
                $table->dropColumn('payment_proof_path');
            }
            if (Schema::hasColumn('commission_invoices', 'payment_note')) {
                $table->dropColumn('payment_note');
            }
            if (Schema::hasColumn('commission_invoices', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });
    }
};
