<?php

namespace Tests\Feature;

use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase6AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_panel()
    {
        $this->get('/admin')
            ->assertRedirectToRoute('login');

        $this->get('/admin/scholarships')
            ->assertRedirectToRoute('login');
    }

    public function test_student_cannot_access_admin_panel()
    {
        $student = User::factory()->create(['is_admin' => false]);

        $this->actingAs($student)
            ->get('/admin')
            ->assertRedirect('/dashboard')
            ->assertSessionHas('error');

        $this->actingAs($student)
            ->get('/admin/scholarships')
            ->assertRedirect('/dashboard')
            ->assertSessionHas('error');
    }

    public function test_admin_can_access_admin_dashboard()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertStatus(200)
            ->assertSee('Registered Students')
            ->assertSee('Active Courses')
            ->assertSee('Pending Scholarships')
            ->assertSee('Published Posts');
    }

    public function test_admin_can_access_scholarship_management()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        ScholarshipApplication::create([
            'name' => 'Test Applicant',
            'email' => 'applicant@example.com',
            'phone' => '+8801700000000',
            'target_course' => 'HSK Level 3',
            'educational_background' => 'HSC (Science)',
            'statement_of_purpose' => 'I am applying for a scholarship to support my Chinese language studies.',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get('/admin/scholarships')
            ->assertStatus(200)
            ->assertSee('Test Applicant');
    }

    public function test_admin_can_update_scholarship_status()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $application = ScholarshipApplication::create([
            'name' => 'Status Applicant',
            'email' => 'status@example.com',
            'phone' => '+8801711223344',
            'target_course' => 'HSK Level 5',
            'educational_background' => 'BBA',
            'statement_of_purpose' => 'I would like to receive a scholarship for advanced Chinese studies.',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->patch("/admin/scholarships/{$application->id}/status", ['status' => 'approved'])
            ->assertRedirect();

        $this->assertDatabaseHas('scholarship_applications', [
            'id' => $application->id,
            'status' => 'approved',
        ]);
    }

    public function test_student_cannot_update_scholarship_status()
    {
        $student = User::factory()->create(['is_admin' => false]);
        $application = ScholarshipApplication::create([
            'name' => 'Protected Applicant',
            'email' => 'protected@example.com',
            'phone' => '+8801722334455',
            'target_course' => 'HSK Level 2',
            'educational_background' => 'SSC',
            'statement_of_purpose' => 'I need financial assistance for my Chinese course.',
            'status' => 'pending',
        ]);

        $this->actingAs($student)
            ->patch("/admin/scholarships/{$application->id}/status", ['status' => 'approved'])
            ->assertRedirect('/dashboard');

        $this->assertDatabaseHas('scholarship_applications', [
            'id' => $application->id,
            'status' => 'pending',
        ]);
    }
}
