<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->string('highest_qualification')->nullable()->after('phone');
            $table->string('gpa_cgpa')->nullable()->after('highest_qualification');
            $table->string('desired_program')->nullable()->after('gpa_cgpa');
            $table->string('target_intake')->nullable()->after('desired_program');
            $table->text('message')->nullable()->after('statement_of_purpose');
        });
    }

    public function down(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->dropColumn(['highest_qualification', 'gpa_cgpa', 'desired_program', 'target_intake', 'message']);
        });
    }
};
