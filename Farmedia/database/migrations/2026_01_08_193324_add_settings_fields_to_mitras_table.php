<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mitras', function (Blueprint $table) {

            // kalau kolom ini sudah ada, hapus bagian yang sama
            if (!Schema::hasColumn('mitras', 'pickup_address')) {
                $table->string('pickup_address')->nullable()->after('alamat');
            }

            if (!Schema::hasColumn('mitras', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('pickup_address');
            }
            if (!Schema::hasColumn('mitras', 'bank_account_number')) {
                $table->string('bank_account_number')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('mitras', 'bank_account_name')) {
                $table->string('bank_account_name')->nullable()->after('bank_account_number');
            }

            if (!Schema::hasColumn('mitras', 'is_holiday')) {
                $table->boolean('is_holiday')->default(false)->after('bank_account_name');
            }
            if (!Schema::hasColumn('mitras', 'holiday_message')) {
                $table->text('holiday_message')->nullable()->after('is_holiday');
            }

            // ✅ untuk logo
            if (!Schema::hasColumn('mitras', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('holiday_message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mitras', function (Blueprint $table) {
            $cols = [
                'pickup_address',
                'bank_name', 'bank_account_number', 'bank_account_name',
                'is_holiday', 'holiday_message',
                'logo_path',
            ];

            foreach ($cols as $c) {
                if (Schema::hasColumn('mitras', $c)) $table->dropColumn($c);
            }
        });
    }
};
