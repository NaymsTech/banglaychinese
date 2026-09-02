<?php

namespace Tests\Feature;

use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_view_services_list(): void
    {
        $this->actingAs($this->admin())
            ->get(ListServices::getUrl())
            ->assertOk();
    }

    public function test_admin_can_view_create_service_page(): void
    {
        $this->actingAs($this->admin())
            ->get(CreateService::getUrl())
            ->assertOk();
    }

    public function test_admin_can_create_a_service_through_the_form(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateService::class)
            ->fillForm([
                'name' => 'Guided Application',
                'price' => 1500,
                'features' => ['University selection', 'Application support'],
                'status' => '1',
                'sort_order' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('services', [
            'slug' => 'guided-application',
            'price' => 1500,
        ]);
    }
}
