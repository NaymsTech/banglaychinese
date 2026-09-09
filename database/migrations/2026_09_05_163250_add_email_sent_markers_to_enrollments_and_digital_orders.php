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
        // Idempotency markers: set atomically with the paid/approved status
        // transition so repeated actions, bulk runs and retries can never
        // queue the same transactional email twice.
        Schema::table('enrollments', function (Blueprint $table) {
            $table->timestamp('confirmation_email_sent_at')->nullable()->after('paid_at');
        });

        Schema::table('digital_orders', function (Blueprint $table) {
            $table->timestamp('approval_email_sent_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('confirmation_email_sent_at');
        });

        Schema::table('digital_orders', function (Blueprint $table) {
            $table->dropColumn('approval_email_sent_at');
        });
    }
};
