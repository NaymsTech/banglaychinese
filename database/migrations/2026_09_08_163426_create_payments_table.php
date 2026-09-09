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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // One row per payment attempt/installment. String channel, not an
            // enum, so future gateway names never require a schema change.
            $table->string('method')->nullable();
            $table->string('trx_reference')->nullable();
            $table->string('sender_number')->nullable();
            $table->text('sender_details')->nullable();

            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('BDT');
            $table->string('status')->default('pending');
            $table->text('review_note')->nullable();

            // Reserved for future payment gateway integrations.
            $table->string('gateway')->nullable();
            $table->string('gateway_ref')->nullable();
            $table->string('gateway_raw_status')->nullable();
            $table->json('callback_payload')->nullable();
            $table->string('proof_path')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
            $table->index('method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
