<?php

namespace Tests\Feature;

use App\Filament\Exports\LeadExporter;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Models\Lead;
use App\Models\User;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeadsResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    protected function lead(array $overrides = []): Lead
    {
        return Lead::factory()->create(array_merge([
            'name' => 'Rahim Uddin',
            'email' => 'rahim@example.com',
            'whatsapp_number' => '01712345678',
            'source' => Lead::SOURCE_CONTACT_FORM,
            'interest' => Lead::INTEREST_GENERAL,
        ], $overrides));
    }

    public function test_admin_can_view_the_leads_list_with_whatsapp_link(): void
    {
        $this->lead();

        $this->actingAs($this->admin())
            ->get(ListLeads::getUrl())
            ->assertOk()
            ->assertSee('Rahim Uddin')
            ->assertSee('rahim@example.com')
            ->assertSee('https://wa.me/01712345678', false);
    }

    public function test_admin_can_filter_leads_by_source(): void
    {
        $checkout = $this->lead([
            'email' => 'buyer@example.com',
            'source' => Lead::SOURCE_CHECKOUT,
            'interest' => Lead::INTEREST_DIGITAL_PRODUCTS,
        ]);
        $contact = $this->lead([
            'email' => 'inquiry@example.com',
            'name' => 'Karim Mia',
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListLeads::class)
            ->filterTable('source', Lead::SOURCE_CHECKOUT)
            ->assertCanSeeTableRecords([$checkout])
            ->assertCanNotSeeTableRecords([$contact]);
    }

    public function test_admin_can_filter_leads_by_subscription_state(): void
    {
        $unsubscribed = $this->lead(['email' => 'optedout@example.com', 'is_subscribed' => false]);
        $subscribed = $this->lead(['email' => 'optedin@example.com']);

        Livewire::actingAs($this->admin())
            ->test(ListLeads::class)
            ->filterTable('is_subscribed', false)
            ->assertCanSeeTableRecords([$unsubscribed])
            ->assertCanNotSeeTableRecords([$subscribed]);
    }

    public function test_admin_can_filter_leads_by_capture_date_range(): void
    {
        $inRange = $this->lead([
            'email' => 'recent@example.com',
            'created_at' => now()->subDays(2),
        ]);
        $outsideRange = $this->lead([
            'email' => 'old@example.com',
            'name' => 'Karim Mia',
            'created_at' => now()->subDays(40),
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListLeads::class)
            ->filterTable('captured_at', [
                'created_from' => now()->subDays(7)->toDateString(),
                'created_until' => now()->toDateString(),
            ])
            ->assertCanSeeTableRecords([$inRange])
            ->assertCanNotSeeTableRecords([$outsideRange]);
    }

    public function test_admin_can_export_filtered_leads_with_native_exporter(): void
    {
        $checkout = $this->lead([
            'name' => 'Rahim Uddin',
            'email' => 'buyer@example.com',
            'source' => Lead::SOURCE_CHECKOUT,
            'interest' => Lead::INTEREST_DIGITAL_PRODUCTS,
            'notes' => 'Follow-up call scheduled',
        ]);
        $this->lead([
            'email' => 'inquiry@example.com',
            'name' => 'Karim Mia',
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListLeads::class)
            ->filterTable('source', Lead::SOURCE_CHECKOUT)
            ->callAction('export');

        // The export action snapshots the currently filtered query for the job.
        $export = Export::where('exporter', LeadExporter::class)->firstOrFail();
        $this->assertSame(1, $export->total_rows);

        // The exporter emits exactly the requested columns in order.
        $columnMap = collect(LeadExporter::getColumns())
            ->mapWithKeys(fn ($column): array => [$column->getName() => $column->getLabel()])
            ->all();

        $this->assertSame(
            ['Name', 'Email', 'WhatsApp', 'Source', 'Interest', 'Notes', 'Created At'],
            array_values($columnMap),
        );

        // And formats each lead's values (including the label mapping).
        $row = array_values(($export->getExporter(columnMap: $columnMap, options: []))($checkout->fresh()));

        $this->assertSame([
            'Rahim Uddin',
            'buyer@example.com',
            '01712345678',
            'Checkout',
            'Digital products',
            'Follow-up call scheduled',
            $checkout->created_at->format('Y-m-d H:i:s'),
        ], $row);
    }
}
