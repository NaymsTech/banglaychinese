<?php

namespace Tests\Feature;

use App\Models\EmailProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmailProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_config_is_encrypted_at_rest_and_decrypted_transparently(): void
    {
        $provider = EmailProvider::factory()->create([
            'config' => [
                'host' => 'smtp-relay.brevo.com',
                'port' => 587,
                'username' => 'smtp-login',
                'password' => 'super-secret-key',
                'from_address' => 'info@banglaychinese.com',
            ],
        ]);

        $stored = DB::table('email_providers')->where('id', $provider->id)->value('config');

        $this->assertIsString($stored);
        $this->assertStringNotContainsString('super-secret-key', $stored);
        $this->assertStringNotContainsString('smtp-login', $stored);

        $fresh = $provider->fresh();

        $this->assertSame('super-secret-key', $fresh->config['password']);
        $this->assertSame('smtp-relay.brevo.com', $fresh->config['host']);
    }

    public function test_updating_the_config_re_encrypts_the_payload(): void
    {
        $provider = EmailProvider::factory()->create(['config' => ['password' => 'old-secret']]);

        $provider->update(['config' => ['password' => 'new-secret', 'host' => 'smtp.example.com']]);

        $stored = DB::table('email_providers')->where('id', $provider->id)->value('config');

        $this->assertSame('new-secret', $provider->fresh()->config['password']);
        $this->assertStringNotContainsString('new-secret', $stored);
        $this->assertStringNotContainsString('old-secret', $stored);
    }

    public function test_a_legacy_plaintext_config_row_is_still_readable_and_is_re_encrypted_on_write(): void
    {
        $id = DB::table('email_providers')->insertGetId([
            'name' => 'Legacy Provider',
            'driver' => EmailProvider::DRIVER_SMTP,
            'config' => json_encode(['host' => 'smtp.example.com', 'password' => 'plaintext-secret']),
            'is_active' => true,
            'priority' => 1,
            'daily_limit' => 0,
            'sent_today' => 0,
            'last_reset' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $provider = EmailProvider::findOrFail($id);

        $this->assertSame('plaintext-secret', $provider->config['password']);

        $provider->config = $provider->config;
        $provider->save();

        $stored = DB::table('email_providers')->where('id', $id)->value('config');

        $this->assertStringNotContainsString('plaintext-secret', $stored);
        $this->assertSame('plaintext-secret', $provider->fresh()->config['password']);
    }

    public function test_only_drivers_with_an_installed_transport_are_offered(): void
    {
        $this->assertSame([
            EmailProvider::DRIVER_SMTP => 'SMTP',
            EmailProvider::DRIVER_SENDGRID => 'SendGrid',
        ], EmailProvider::DRIVERS);

        $this->assertNotContains('resend', EmailProvider::DRIVERS);
        $this->assertNotContains('mailgun', EmailProvider::DRIVERS);
    }

    public function test_the_unused_is_default_column_has_been_removed(): void
    {
        $this->assertFalse(Schema::hasColumn('email_providers', 'is_default'));
    }

    public function test_redact_secrets_replaces_every_stored_credential(): void
    {
        $provider = EmailProvider::factory()->create([
            'config' => [
                'password' => 'hunter2-secret',
                'api_key' => 'SG.api-key-123',
            ],
        ]);

        $this->assertSame(
            'Login rejected for [REDACTED] with key [REDACTED].',
            $provider->redactSecrets('Login rejected for hunter2-secret with key SG.api-key-123.'),
        );
    }

    public function test_redact_secrets_leaves_other_messages_untouched_and_handles_null(): void
    {
        $provider = EmailProvider::factory()->create(['config' => ['password' => 'hunter2-secret']]);

        $this->assertNull($provider->redactSecrets(null));
        $this->assertSame('a totally different failure', $provider->redactSecrets('a totally different failure'));
    }
}
