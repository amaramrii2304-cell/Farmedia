<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // status pembayaran transfer: pending/submitted/verified/rejected
            if (!Schema::hasColumn('orders', 'transfer_status')) {
                $table->string('transfer_status', 32)->nullable()->after('metode_pembayaran');
            }

            if (!Schema::hasColumn('orders', 'transfer_amount')) {
                $table->integer('transfer_amount')->nullable()->after('transfer_status');
            }

            if (!Schema::hasColumn('orders', 'transfer_deadline_at')) {
                $table->dateTime('transfer_deadline_at')->nullable()->after('transfer_amount');
            }

            // info rekening platform (bisa dikirim ke Android)
            if (!Schema::hasColumn('orders', 'transfer_bank')) {
                $table->string('transfer_bank', 255)->nullable()->after('transfer_deadline_at');
            }
            if (!Schema::hasColumn('orders', 'transfer_account_no')) {
                $table->string('transfer_account_no', 100)->nullable()->after('transfer_bank');
            }
            if (!Schema::hasColumn('orders', 'transfer_account_name')) {
                $table->string('transfer_account_name', 255)->nullable()->after('transfer_account_no');
            }

            // bukti transfer
            if (!Schema::hasColumn('orders', 'transfer_proof_path')) {
                $table->string('transfer_proof_path', 500)->nullable()->after('transfer_account_name');
            }
            if (!Schema::hasColumn('orders', 'transfer_notes')) {
                $table->text('transfer_notes')->nullable()->after('transfer_proof_path');
            }

            if (!Schema::hasColumn('orders', 'transfer_submitted_at')) {
                $table->dateTime('transfer_submitted_at')->nullable()->after('transfer_notes');
            }
            if (!Schema::hasColumn('orders', 'transfer_verified_at')) {
                $table->dateTime('transfer_verified_at')->nullable()->after('transfer_submitted_at');
            }
            if (!Schema::hasColumn('orders', 'transfer_rejected_at')) {
                $table->dateTime('transfer_rejected_at')->nullable()->after('transfer_verified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $cols = [
                'transfer_status',
                'transfer_amount',
                'transfer_deadline_at',
                'transfer_bank',
                'transfer_account_no',
                'transfer_account_name',
                'transfer_proof_path',
                'transfer_notes',
                'transfer_submitted_at',
                'transfer_verified_at',
                'transfer_rejected_at',
            ];

            foreach ($cols as $c) {
                if (Schema::hasColumn('orders', $c)) $table->dropColumn($c);
            }
        });
    }
};
