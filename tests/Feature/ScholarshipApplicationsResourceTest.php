<?php

namespace Tests\Feature;

use App\Filament\Resources\ScholarshipApplications\Pages\EditScholarshipApplication;
use App\Filament\Resources\ScholarshipApplications\Pages\ListScholarshipApplications;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScholarshipApplicationsResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    protected function application(array $overrides = []): ScholarshipApplication
    {
        return ScholarshipApplication::create(array_merge([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '01711111111',
            'desired_program' => 'BSc Computer Science',
            'status' => 'new',
            'message' => 'I want to study in China.',
        ], $overrides));
    }

    public function test_admin_can_view_applications_list(): void
    {
        $this->application();

        $this->actingAs($this->admin())
            ->get(ListScholarshipApplications::getUrl())
            ->assertOk()
            ->assertSee('Jane Doe')
            ->assertSee('BSc Computer Science');
    }

    public function test_admin_can_move_lead_through_crm_and_set_scholarship_outcome(): void
    {
        $application = $this->application();

        Livewire::actingAs($this->admin())
            ->test(EditScholarshipApplication::class, ['record' => $application->getRouteKey()])
            ->fillForm([
                'status' => 'consultation_scheduled',
                'application_status' => 'approved',
                'admin_notes' => 'Consultation booked for Friday.',
                'follow_up_date' => '2026-10-15',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $application->refresh();

        $this->assertSame('consultation_scheduled', $application->status);
        $this->assertSame('approved', $application->application_status);
        $this->assertSame('Consultation booked for Friday.', $application->admin_notes);
        $this->assertNotNull($application->follow_up_date);
    }

    public function test_pending_application_status_is_stored_as_null(): void
    {
        $application = $this->application(['application_status' => 'pending']);

        $this->assertNull($application->fresh()->application_status);

        $application->update(['application_status' => 'rejected']);

        $this->assertSame('rejected', $application->fresh()->application_status);
    }
}
