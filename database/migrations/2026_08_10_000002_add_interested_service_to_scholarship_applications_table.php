<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('scholarship_applications', 'interested_service_id')) {
                $table->unsignedBigInteger('interested_service_id')->nullable()->after('desired_program');
                $table->foreign('interested_service_id')
                    ->references('id')
                    ->on('services')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            if (Schema::hasColumn('scholarship_applications', 'interested_service_id')) {
                $table->dropForeign(['interested_service_id']);
                $table->dropColumn('interested_service_id');
            }
        });
    }
};
