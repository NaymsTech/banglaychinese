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
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->string('student_name');
            $table->string('student_email');
            $table->string('student_phone');
            $table->decimal('amount', 8, 2);
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending');
            $table->string('enrollment_status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['payment_status']);
            $table->index(['enrollment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
