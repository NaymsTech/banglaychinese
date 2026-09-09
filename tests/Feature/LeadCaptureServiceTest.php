<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use App\Services\LeadCaptureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadCaptureServiceTest extends TestCase
{
    use RefreshDatabase;

    private function capture(array $data): Lead
    {
        return app(LeadCaptureService::class)->capture($data);
    }

    public function test_first_capture_creates_a_lead_with_defaults(): void
    {
        $lead = $this->capture([
            'email' => '  Rahim@Example.COM ',
        ]);

        $this->assertDatabaseCount('leads', 1);
        $this->assertSame('rahim@example.com', $lead->email);
        $this->assertSame('rahim', $lead->name);
        $this->assertSame(Lead::SOURCE_WEBSITE, $lead->source);
        $this->assertSame(Lead::INTEREST_GENERAL, $lead->interest);
        $this->assertTrue($lead->is_subscribed);
    }

    public function test_same_email_updates_the_lead_instead_of_creating_a_duplicate(): void
    {
        $this->capture([
            'email' => 'rahim@example.com',
            'name' => 'Rahim Uddin',
            'whatsapp_number' => '+880 1712-345678',
            'source' => Lead::SOURCE_CONTACT_FORM,
            'interest' => Lead::INTEREST_GENERAL,
        ]);

        $lead = $this->capture([
            'email' => 'rahim@example.com',
            'name' => 'Rahim Ahmed',
            'interest' => Lead::INTEREST_COURSES,
        ]);

        $this->assertDatabaseCount('leads', 1);
        $this->assertSame('Rahim Ahmed', $lead->name);
        $this->assertSame(Lead::INTEREST_COURSES, $lead->interest);
        // Enrichment must not wipe data the new interaction did not provide.
        $this->assertSame('8801712345678', $lead->whatsapp_number);
        $this->assertSame(Lead::SOURCE_CONTACT_FORM, $lead->source);
    }

    public function test_capture_links_a_lead_to_its_registered_user(): void
    {
        $user = User::factory()->create([
            'email' => 'student@example.com',
            'phone' => '01712345678',
        ]);

        $lead = $this->capture([
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'source' => Lead::SOURCE_REGISTRATION,
        ]);

        $this->assertDatabaseCount('leads', 1);
        $this->assertSame($user->id, $lead->user_id);
    }

    public function test_capture_does_not_resubscribe_a_lead_that_opted_out(): void
    {
        $lead = $this->capture(['email' => 'rahim@example.com']);

        $lead->forceFill(['is_subscribed' => false])->save();

        $this->capture([
            'email' => 'rahim@example.com',
            'interest' => Lead::INTEREST_COURSES,
        ]);

        $this->assertFalse($lead->fresh()->is_subscribed);
    }

    public function test_capture_with_is_subscribed_false_creates_an_unsubscribed_lead(): void
    {
        $lead = $this->capture([
            'email' => 'rahim@example.com',
            'is_subscribed' => false,
        ]);

        $this->assertFalse($lead->is_subscribed);
    }

    public function test_capture_never_overwrites_admin_written_notes(): void
    {
        $this->capture([
            'email' => 'rahim@example.com',
            'notes' => 'Follow up after results are published.',
        ]);

        $lead = $this->capture([
            'email' => 'rahim@example.com',
            'notes' => 'Sent the brochure again.',
        ]);

        $this->assertSame('Follow up after results are published.', $lead->notes);
    }
}
