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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Polymorphic reference to the purchasable (Course, Product,
            // Service). No DB-level FK: snapshots below keep the sale intact.
            $table->string('purchasable_type');
            $table->unsignedBigInteger('purchasable_id');
            $table->string('title');
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity')->default(1);

            $table->timestamps();

            $table->index(['purchasable_type', 'purchasable_id']);
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
