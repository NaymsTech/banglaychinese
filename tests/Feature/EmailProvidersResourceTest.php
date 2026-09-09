<?php

namespace Tests\Feature;

use App\Filament\Resources\EmailProviders\Pages\CreateEmailProvider;
use App\Filament\Resources\EmailProviders\Pages\EditEmailProvider;
use App\Filament\Resources\EmailProviders\Pages\ListEmailProviders;
use App\Models\EmailProvider;
use App\Models\User;
use App\Services\EmailService;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class EmailProvidersResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    protected function provider(array $overrides = []): EmailProvider
    {
        return EmailProvider::factory()->create(array_merge([
            'name' => 'Brevo',
            'driver' => EmailProvider::DRIVER_SMTP,
        ], $overrides));
    }

    public function test_admin_can_open_the_provider_list(): void
    {
        $provider = $this->provider();

        $this->actingAs($this->admin())
            ->get(ListEmailProviders::getUrl())
            ->assertOk()
            ->assertSee('Brevo')
            ->assertSee('SMTP');
    }

    public function test_admin_can_create_an_smtp_provider_with_credentials(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateEmailProvider::class)
            ->fillForm([
                'name' => 'SendGrid via SMTP',
                'driver' => EmailProvider::DRIVER_SMTP,
                'priority' => '2',
                'daily_limit' => '300',
                'config' => [
                    'host' => 'smtp.sendgrid.net',
                    'port' => '587',
                    'encryption' => 'tls',
                    'username' => 'apikey',
                    'password' => 'secret-key',
                    'from_address' => 'info@banglaychinese.com',
                    'from_name' => 'Banglay Chinese',
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $provider = EmailProvider::where('name', 'SendGrid via SMTP')->firstOrFail();

        $this->assertSame(EmailProvider::DRIVER_SMTP, $provider->driver);
        $this->assertSame('smtp.sendgrid.net', $provider->config['host']);
        $this->assertSame('secret-key', $provider->config['password']);
        $this->assertSame(2, $provider->priority);
        $this->assertSame(300, $provider->daily_limit);
        $this->assertTrue($provider->is_active);
    }

    public function test_admin_can_send_a_test_email_from_the_provider_row(): void
    {
        $admin = $this->admin();
        $provider = $this->provider(['name' => 'Brevo 2']);

        $service = Mockery::mock(EmailService::class, [app('mail.manager')])->makePartial();
        $service->shouldReceive('sendViaProvider')->once();

        $this->app->instance(EmailService::class, $service);

        Livewire::actingAs($admin)
            ->test(ListEmailProviders::class)
            ->callAction(TestAction::make('testConnection')->table($provider))
            ->assertNotified();
    }

    public function test_admin_can_create_a_sendgrid_provider_with_an_api_key(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateEmailProvider::class)
            ->fillForm([
                'name' => 'SendGrid API',
                'driver' => EmailProvider::DRIVER_SENDGRID,
                'config' => [
                    'api_key' => 'SG.sendgrid-api-key-123',
                    'from_address' => 'info@banglaychinese.com',
                    'from_name' => 'Banglay Chinese',
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $provider = EmailProvider::where('name', 'SendGrid API')->firstOrFail();

        $this->assertSame(EmailProvider::DRIVER_SENDGRID, $provider->driver);
        $this->assertSame('SG.sendgrid-api-key-123', $provider->config['api_key']);
        $this->assertSame('SendGrid', EmailProvider::DRIVERS[EmailProvider::DRIVER_SENDGRID]);
    }

    public function test_editing_a_provider_never_renders_the_stored_secret_and_keeps_it_when_left_blank(): void
    {
        $provider = $this->provider([
            'config' => [
                'host' => 'smtp-relay.brevo.com',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'smtp-login',
                'password' => 'existing-secret-key',
                'from_address' => 'info@banglaychinese.com',
                'from_name' => 'Banglay Chinese',
            ],
        ]);

        $this->actingAs($this->admin())
            ->get(EditEmailProvider::getUrl(['record' => $provider->getRouteKey()]))
            ->assertOk()
            ->assertDontSee('existing-secret-key');

        Livewire::actingAs($this->admin())
            ->test(EditEmailProvider::class, ['record' => $provider->getRouteKey()])
            ->fillForm([
                'config' => [
                    'host' => 'smtp.eu.brevo.com',
                    'port' => '587',
                    'encryption' => 'tls',
                    'username' => 'smtp-login',
                    'from_address' => 'info@banglaychinese.com',
                    'from_name' => 'Banglay Chinese',
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $fresh = $provider->fresh();

        $this->assertSame('existing-secret-key', $fresh->config['password']);
        $this->assertSame('smtp.eu.brevo.com', $fresh->config['host']);
    }

    public function test_admin_can_replace_the_stored_secret_by_entering_a_new_one(): void
    {
        $provider = $this->provider([
            'config' => [
                'host' => 'smtp-relay.brevo.com',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'smtp-login',
                'password' => 'old-secret-key',
                'from_address' => 'info@banglaychinese.com',
                'from_name' => 'Banglay Chinese',
            ],
        ]);

        Livewire::actingAs($this->admin())
            ->test(EditEmailProvider::class, ['record' => $provider->getRouteKey()])
            ->fillForm([
                'config' => [
                    'host' => 'smtp-relay.brevo.com',
                    'port' => '587',
                    'encryption' => 'tls',
                    'username' => 'smtp-login',
                    'password' => 'brand-new-secret-key',
                    'from_address' => 'info@banglaychinese.com',
                    'from_name' => 'Banglay Chinese',
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('brand-new-secret-key', $provider->fresh()->config['password']);
    }
}
