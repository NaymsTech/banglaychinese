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
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->string('target_course')->nullable()->change();
            $table->text('educational_background')->nullable()->change();
            $table->text('statement_of_purpose')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->string('target_course')->nullable(false)->change();
            $table->text('educational_background')->nullable(false)->change();
            $table->text('statement_of_purpose')->nullable(false)->change();
        });
    }
};
