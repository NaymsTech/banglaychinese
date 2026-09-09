<?php

namespace Tests\Feature;

use App\Filament\Resources\ServiceOrders\Pages\CreateServiceOrder;
use App\Filament\Resources\ServiceOrders\Pages\ListServiceOrders;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceOrdersResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    protected function package(): Service
    {
        return Service::create([
            'name' => 'Guided Application',
            'slug' => 'guided-application',
            'price' => 25000,
            'status' => true,
        ]);
    }

    protected function order(Service $service, array $overrides = []): ServiceOrder
    {
        return ServiceOrder::create(array_merge([
            'service_id' => $service->id,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'amount' => 25000,
            'payment_method' => 'bkash',
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
        ], $overrides));
    }

    public function test_admin_can_open_service_order_list_and_create_page(): void
    {
        $admin = $this->admin();
        $this->package();

        $this->actingAs($admin)->get(ListServiceOrders::getUrl())->assertOk();
        $this->actingAs($admin)->get(CreateServiceOrder::getUrl())->assertOk();
    }

    public function test_admin_can_create_a_service_order_through_the_form(): void
    {
        $service = $this->package();

        Livewire::actingAs($this->admin())
            ->test(CreateServiceOrder::class)
            ->fillForm([
                'service_id' => (string) $service->id,
                'student_name' => 'Karim Ahmed',
                'student_email' => 'karim@example.com',
                'student_phone' => '01812345678',
                'amount' => 65000,
                'payment_method' => 'bkash',
                'payment_status' => 'pending',
                'enrollment_status' => 'pending',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('service_orders', [
            'service_id' => $service->id,
            'student_name' => 'Karim Ahmed',
            'student_email' => 'karim@example.com',
            'amount' => 65000,
            'payment_method' => 'bkash',
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
        ]);
    }

    public function test_mark_as_paid_action_pays_and_starts_the_enrollment(): void
    {
        $order = $this->order($this->package());

        Livewire::actingAs($this->admin())
            ->test(ListServiceOrders::class)
            ->callTableAction('markPaid', $order);

        $order->refresh();

        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('in_progress', $order->enrollment_status);
    }

    public function test_bulk_mark_completed_completes_selected_orders(): void
    {
        $service = $this->package();
        $first = $this->order($service);
        $second = $this->order($service, ['payment_status' => 'paid', 'enrollment_status' => 'in_progress']);

        Livewire::actingAs($this->admin())
            ->test(ListServiceOrders::class)
            ->callTableBulkAction('bulkMarkCompleted', [$first->id, $second->id]);

        $this->assertSame('completed', $first->fresh()->enrollment_status);
        $this->assertSame('completed', $second->fresh()->enrollment_status);
    }
}
