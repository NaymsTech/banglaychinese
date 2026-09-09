<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Editable contact/social fields for the centralized email branding row.
     * The WhatsApp link is never stored — the country code and number are kept
     * separately and EmailBranding builds the canonical https://wa.me/… URL at
     * render time. Blank values intentionally inherit the site settings that
     * the email footer used before this configuration existed.
     */
    public function up(): void
    {
        Schema::table('email_branding', function (Blueprint $table) {
            $table->string('whatsapp_country_code', 8)->default('')->after('contact_phone');
            $table->string('whatsapp_number', 24)->default('')->after('whatsapp_country_code');
            $table->string('facebook_url', 255)->default('')->after('whatsapp_number');
            $table->string('instagram_url', 255)->default('')->after('facebook_url');
            $table->string('youtube_url', 255)->default('')->after('instagram_url');
        });
    }

    public function down(): void
    {
        Schema::table('email_branding', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_country_code', 'whatsapp_number', 'facebook_url', 'instagram_url', 'youtube_url']);
        });
    }
};
