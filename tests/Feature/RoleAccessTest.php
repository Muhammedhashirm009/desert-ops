<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Account;
use App\Models\Material;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected $shouldAuthenticate = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic Chart of Accounts required by accounting dashboard
        Account::create(['code' => '1010', 'name' => 'Cash', 'type' => 'asset']);
        Account::create(['code' => '1020', 'name' => 'Bank', 'type' => 'asset']);
    }

    private function createUser($role)
    {
        return User::factory()->create([
            'role' => $role,
        ]);
    }

    public function test_admin_has_full_access()
    {
        $admin = $this->createUser('admin');

        // Admin can access central dashboard
        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertStatus(200);

        // Admin can access user management
        $response = $this->actingAs($admin)->get(route('admin.users.index'));
        $response->assertStatus(200);

        // Admin can access suppliers
        $response = $this->actingAs($admin)->get(route('suppliers.index'));
        $response->assertStatus(200);

        // Admin can access accounting portal
        $response = $this->actingAs($admin)->get(route('accounting.dashboard'));
        $response->assertStatus(200);

        // Admin can access materials creation
        $response = $this->actingAs($admin)->get(route('materials.create'));
        $response->assertStatus(200);
    }

    public function test_accountant_access()
    {
        $accountant = $this->createUser('accountant');

        // Accountant is redirected from central dashboard to accounting portal
        $response = $this->actingAs($accountant)->get(route('dashboard'));
        $response->assertRedirect(route('accounting.dashboard'));

        // Accountant can access accounting dashboard
        $response = $this->actingAs($accountant)->get(route('accounting.dashboard'));
        $response->assertStatus(200);

        // Accountant cannot access user management
        $response = $this->actingAs($accountant)->get(route('admin.users.index'));
        $response->assertStatus(403);

        // Accountant cannot access suppliers list
        $response = $this->actingAs($accountant)->get(route('suppliers.index'));
        $response->assertStatus(403);
    }

    public function test_gm_access()
    {
        $gm = $this->createUser('gm');

        // GM can access central dashboard
        $response = $this->actingAs($gm)->get(route('dashboard'));
        $response->assertStatus(200);

        // GM can access suppliers
        $response = $this->actingAs($gm)->get(route('suppliers.index'));
        $response->assertStatus(200);

        // GM cannot access accounting portal
        $response = $this->actingAs($gm)->get(route('accounting.dashboard'));
        $response->assertStatus(403);

        // GM cannot access user management
        $response = $this->actingAs($gm)->get(route('admin.users.index'));
        $response->assertStatus(403);
    }

    public function test_kitchen_chef_access()
    {
        $chef = $this->createUser('kitchen_chef');

        // Chef can access production runs index
        $response = $this->actingAs($chef)->get(route('production-runs.index'));
        $response->assertStatus(200);

        // Chef can access materials index (view-only)
        $response = $this->actingAs($chef)->get(route('materials.index'));
        $response->assertStatus(200);

        // Chef cannot access suppliers list
        $response = $this->actingAs($chef)->get(route('suppliers.index'));
        $response->assertStatus(403);

        // Chef cannot access accounting portal
        $response = $this->actingAs($chef)->get(route('accounting.dashboard'));
        $response->assertStatus(403);

        // Chef cannot access user management
        $response = $this->actingAs($chef)->get(route('admin.users.index'));
        $response->assertStatus(403);

        // Chef cannot access materials creation
        $response = $this->actingAs($chef)->get(route('materials.create'));
        $response->assertStatus(403);
    }

    public function test_store_manager_access()
    {
        $manager = $this->createUser('store_manager');

        // Manager can access suppliers
        $response = $this->actingAs($manager)->get(route('suppliers.index'));
        $response->assertStatus(200);

        // Manager can access purchase orders index
        $response = $this->actingAs($manager)->get(route('purchase-orders.index'));
        $response->assertStatus(200);

        // Manager cannot access production runs index
        $response = $this->actingAs($manager)->get(route('production-runs.index'));
        $response->assertStatus(403);

        // Manager cannot access accounting portal
        $response = $this->actingAs($manager)->get(route('accounting.dashboard'));
        $response->assertStatus(403);

        // Manager cannot access user management
        $response = $this->actingAs($manager)->get(route('admin.users.index'));
        $response->assertStatus(403);

        // Manager can access materials creation
        $response = $this->actingAs($manager)->get(route('materials.create'));
        $response->assertStatus(200);
    }

    public function test_material_request_creation_triggers_notifications_to_authorized_roles()
    {
        \Illuminate\Support\Facades\Notification::fake();

        // Create recipient users first so they are present in the database when queried
        $admin = $this->createUser('admin');
        $gm = $this->createUser('gm');
        $manager = $this->createUser('store_manager');
        $otherChef = $this->createUser('kitchen_chef');

        $chef = $this->createUser('kitchen_chef');
        $material = Material::create([
            'name' => 'Salt',
            'sku' => 'RAW-SALT',
            'category' => 'ingredient',
            'unit' => 'kg',
            'current_stock' => 10,
            'min_stock_alert' => 1,
        ]);

        $response = $this->actingAs($chef)->post(route('material-requests.store'), [
            'requested_by' => 'Chef Suresh',
            'requested_date' => now()->toDateString(),
            'notes' => 'Test request notes',
            'items' => [
                [
                    'material_id' => $material->id,
                    'quantity_requested' => 5,
                ]
            ]
        ]);

        $response->assertRedirect();

        \Illuminate\Support\Facades\Notification::assertSentTo(
            [$admin, $gm, $manager],
            \App\Notifications\MaterialRequestCreated::class
        );

        \Illuminate\Support\Facades\Notification::assertNotSentTo(
            [$otherChef],
            \App\Notifications\MaterialRequestCreated::class
        );
    }

    public function test_material_request_release_triggers_notifications_to_authorized_roles()
    {
        \Illuminate\Support\Facades\Notification::fake();

        // Create recipient users first
        $admin = $this->createUser('admin');
        $gm = $this->createUser('gm');
        $otherChef = $this->createUser('kitchen_chef');
        $otherManager = $this->createUser('store_manager');

        $chef = $this->createUser('kitchen_chef');
        $manager = $this->createUser('store_manager');

        $material = Material::create([
            'name' => 'Salt',
            'sku' => 'RAW-SALT',
            'category' => 'ingredient',
            'unit' => 'kg',
            'current_stock' => 10,
            'min_stock_alert' => 1,
        ]);

        $materialRequest = \App\Models\MaterialRequest::create([
            'request_number' => 'MR-2026-9999',
            'requested_by' => 'Chef Suresh',
            'requested_date' => now(),
            'status' => 'approved',
        ]);

        \App\Models\MaterialRequestItem::create([
            'material_request_id' => $materialRequest->id,
            'material_id' => $material->id,
            'quantity_requested' => 5,
            'quantity_released' => 0,
        ]);

        $response = $this->actingAs($manager)->post(route('material-requests.release', $materialRequest->id), [
            'items' => [
                [
                    'material_id' => $material->id,
                    'quantity_released' => 5,
                ]
            ]
        ]);

        $response->assertRedirect();

        \Illuminate\Support\Facades\Notification::assertSentTo(
            [$admin, $gm, $otherChef],
            \App\Notifications\MaterialRequestReleased::class
        );

        \Illuminate\Support\Facades\Notification::assertNotSentTo(
            [$otherManager],
            \App\Notifications\MaterialRequestReleased::class
        );
    }

    public function test_receiving_shipment_triggers_notifications_to_authorized_roles()
    {
        \Illuminate\Support\Facades\Notification::fake();

        // Create recipient users first
        $admin = $this->createUser('admin');
        $gm = $this->createUser('gm');
        $chef = $this->createUser('kitchen_chef');
        $manager = $this->createUser('store_manager');
        $accountant = $this->createUser('accountant');

        $outlet = \App\Models\Outlet::create([
            'name' => 'Test Outlet',
            'type' => 'own',
            'commission_rate' => 0.00,
            'email' => 'test@dessertops.com',
            'password' => bcrypt('password'),
        ]);

        $dispatch = \App\Models\Dispatch::create([
            'dispatch_number' => 'DISP-2026-TEST-REC',
            'outlet_id' => $outlet->id,
            'dispatch_date' => now(),
            'status' => 'dispatched',
        ]);

        $response = $this->actingAs($outlet, 'outlet')
            ->post(route('portal.dispatches.receive', $dispatch->id));

        $response->assertRedirect();

        \Illuminate\Support\Facades\Notification::assertSentTo(
            [$admin, $gm, $chef],
            \App\Notifications\ShipmentReceived::class
        );

        \Illuminate\Support\Facades\Notification::assertNotSentTo(
            [$manager, $accountant],
            \App\Notifications\ShipmentReceived::class
        );
    }
}

