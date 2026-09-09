<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->decimal('amount_paid', 8, 2)->default(0)->after('amount');
            $table->decimal('amount_due', 8, 2)->default(0)->after('amount_paid');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->decimal('amount_paid', 8, 2)->default(0)->after('price_paid');
            $table->decimal('amount_due', 8, 2)->default(0)->after('amount_paid');
        });

        // Backfill existing service orders: fully-paid rows keep nothing due,
        // everything else still owes the full amount until the admin edits it.
        DB::table('service_orders')->update([
            'amount_paid' => DB::raw("CASE WHEN payment_status = 'paid' THEN amount ELSE 0 END"),
        ]);
        DB::table('service_orders')->update([
            'amount_due' => DB::raw('amount - amount_paid'),
        ]);

        // Course enrollment amounts are backfilled in the next migration, once
        // the legacy price_paid/status columns have been mapped to the new fields.
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'amount_due']);
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'amount_due']);
        });
    }
};
