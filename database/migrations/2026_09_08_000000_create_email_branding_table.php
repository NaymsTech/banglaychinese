<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The single source of truth for the visual/design layer of every branded
     * email. Only ONE row is ever used (id = 1, see
     * App\Models\EmailBranding::SINGLETON_ID); the row is created lazily with
     * built-in defaults by the seeder or the first Filament save.
     */
    public function up(): void
    {
        Schema::create('email_branding', function (Blueprint $table) {
            $table->id();
            $table->string('logo')->nullable()->comment('Relative public-disk path or absolute URL of the email header logo. Empty inherits the site logo setting, then the bundled PNG.');
            $table->string('app_name', 255)->nullable()->comment('Empty inherits the site name setting.');
            $table->string('tagline', 255)->nullable()->comment('Shown under the logo and in the footer. Empty uses the built-in email tagline.');
            $table->string('primary_color', 20)->nullable()->comment('Brand colour: header tagline, top accent strip and accent links.');
            $table->string('accent_color', 20)->nullable()->comment('Deep footer band colour behind the contact/social links.');
            $table->string('body_text_color', 20)->nullable()->comment('Main message text colour on the white card.');
            $table->string('muted_text_color', 20)->nullable()->comment('Quiet footnote/helper text colour.');
            $table->string('footer_text', 500)->nullable()->comment('Optional plain-text line in the footer band above the copyright.');
            $table->string('contact_email', 255)->nullable()->comment('Empty inherits the site contact email setting.');
            $table->string('contact_phone', 40)->nullable()->comment('Optional phone shown in the quiet post-script row.');
            $table->string('website_url', 255)->nullable()->comment('Empty links the logo/website to the app URL/origin.');
            $table->string('copyright_text', 255)->nullable()->comment('Empty renders "© {year} {app name}. All rights reserved."');
            $table->boolean('is_active')->default(true)->comment('When disabled, emails render with the built-in default branding.');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_branding');
    }
};
