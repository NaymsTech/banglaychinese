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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Traceability back to the legacy sale this order will represent
            // once Phase 2 backfills enrollments/digital_orders/service_orders.
            $table->string('legacy_source')->nullable();
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->unique(['legacy_source', 'legacy_id']);

            // Buyer snapshot, copied at sale time. Guest and admin-recorded
            // sales have no linked account.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('student_name')->nullable();
            $table->string('student_email')->nullable();
            $table->string('student_phone')->nullable();

            // Contract price. Received amounts are never stored on the order;
            // paid/refunded totals are derived from the payments table.
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('BDT');
            $table->string('order_status')->default('pending');
            $table->text('admin_notes')->nullable();

            // Per-sale transactional email markers.
            $table->timestamp('order_received_email_sent_at')->nullable();
            $table->timestamp('outcome_email_sent_at')->nullable();
            $table->timestamp('rejection_email_sent_at')->nullable();
            $table->timestamp('attention_email_sent_at')->nullable();
            $table->timestamp('payment_reminder_sent_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
