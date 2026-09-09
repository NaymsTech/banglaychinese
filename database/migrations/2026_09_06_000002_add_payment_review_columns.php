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
            $table->text('attention_reason')->nullable()->after('rejection_reason');
            $table->timestamp('order_received_email_sent_at')->nullable()->after('confirmation_email_sent_at');
            $table->timestamp('rejection_email_sent_at')->nullable()->after('order_received_email_sent_at');
            $table->timestamp('attention_email_sent_at')->nullable()->after('rejection_email_sent_at');
        });

        Schema::table('digital_orders', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('trx_id');
            $table->text('attention_reason')->nullable()->after('rejection_reason');
            $table->timestamp('order_received_email_sent_at')->nullable()->after('approval_email_sent_at');
            $table->timestamp('rejection_email_sent_at')->nullable()->after('order_received_email_sent_at');
            $table->timestamp('attention_email_sent_at')->nullable()->after('rejection_email_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'attention_reason',
                'order_received_email_sent_at',
                'rejection_email_sent_at',
                'attention_email_sent_at',
            ]);
        });

        Schema::table('digital_orders', function (Blueprint $table) {
            $table->dropColumn([
                'rejection_reason',
                'attention_reason',
                'order_received_email_sent_at',
                'rejection_email_sent_at',
                'attention_email_sent_at',
            ]);
        });
    }
};
