<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UsersResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_view_users_list(): void
    {
        $student = User::factory()->create([
            'name' => 'Tanvir Ahmed',
            'role' => 'student',
            'is_admin' => false,
        ]);

        $this->actingAs($this->admin())
            ->get(ListUsers::getUrl())
            ->assertOk()
            ->assertSee('Tanvir Ahmed');
    }

    public function test_admin_can_create_a_user_through_the_form(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'Nusrat Jahan',
                'email' => 'nusrat@example.com',
                'phone' => '01711111111',
                'password' => 'secret123',
                'role' => 'student',
                'is_admin' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'nusrat@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('student', $user->role);
        $this->assertFalse($user->is_admin);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_password_is_required_when_creating_a_user(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'No Password',
                'email' => 'nopass@example.com',
                'role' => 'student',
                'is_admin' => false,
            ])
            ->call('create')
            ->assertHasFormErrors(['password' => 'required']);
    }

    public function test_admin_cannot_remove_own_admin_access(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $admin->getKey()])
            ->fillForm([
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'student',
                'is_admin' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($admin->fresh()->is_admin);
    }
}
