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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('sender_number', 20)->nullable()->after('transaction_id');
            $table->index('transaction_id');
            $table->index('payment_method');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex(['transaction_id']);
            $table->dropIndex(['payment_method']);
            $table->dropIndex(['status']);
            $table->dropColumn('sender_number');
        });
    }
};
