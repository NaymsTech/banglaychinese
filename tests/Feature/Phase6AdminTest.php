<?php

namespace Tests\Feature;

use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase6AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_filament_login(): void
    {
        $this->get('/admin')
            ->assertRedirect('/admin/login');
    }

    public function test_student_cannot_access_admin_panel(): void
    {
        $student = User::factory()->create([
            'is_admin' => false,
            'role' => 'student',
        ]);

        // Filament gates access via the User model's canAccessPanel().
        $this->actingAs($student)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_can_access_filament_dashboard(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_admin_can_view_applications_list(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'role' => 'admin',
        ]);

        ScholarshipApplication::create([
            'name' => 'Test Applicant',
            'email' => 'applicant@example.com',
            'phone' => '+8801700000000',
            'status' => 'new',
        ]);

        $this->actingAs($admin)
            ->get('/admin/scholarship-applications')
            ->assertOk()
            ->assertSee('Test Applicant');
    }
}
