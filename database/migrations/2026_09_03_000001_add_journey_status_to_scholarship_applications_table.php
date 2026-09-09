<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Track the post-application journey of a scholarship candidate, separate
     * from the pre-sale CRM pipeline (`status`) and the scholarship review
     * outcome (`application_status`).
     *
     * journey_status: new → documents_reviewed → applied_to_university →
     * offer_received → visa_processing → approved | rejected
     */
    public function up(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->string('journey_status')->default('new')->after('application_status');
        });
    }

    public function down(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->dropColumn('journey_status');
        });
    }
};
